<?php
class Server{
 private $ports;
 private $log;
 private $error_handler;
 private $socket;
 private $console;
 private $max_connection;
 private $clients;
 private $vhost_storige;
 private $plugin_storige;
 private $config;
 public function __construct(\Server\Config $config,\Util\ClassLoader $class_loader,\Util\ErrorHandler $error_handler,$data_dir){
  $this->config = $config;
  $this->ports = $this->config->get("ports");
  $this->max_connection = $this->config->get("max_connection");
  $this->log = new \Util\Log();
  $this->error_handler = $error_handler;
  $this->vhost_storige = new \Util\PluginStorige($config,$this->log,$class_loader,$data_dir,\Util\PluginStorige::TYPE_VHOST);
  $this->plugin_storige = new \Util\PluginStorige($config,$this->log,$class_loader,$data_dir,\Util\PluginStorige::TYPE_PLUGIN);
  $this->socket = null;
  $this->clients = new \Threaded();
  $this->console = new \Console($this->clients,$this->vhost_storige);
 }
 public function loadVhosts(){
  $this->log->log("Loading all Vhosts");
  $this->vhost_storige->loadAll();
  if(is_null($this->vhost_storige->getBase())){
   $this->log->log("Base vhost not found!","error");
   return false;
  }
  return true;
 }
 public function loadPlugins(){
  $this->log->log("Loading all Plugins");
  $this->plugin_storige->loadAll();
 }
 public function run(){
  $this->socket = array();
  foreach($this->ports as $port){
   $this->log->log("Starting server on port: ".$port);
   $errno = null;
   $errstr = null;
   try{
    $this->socket[$port] = \stream_socket_server("tcp://0.0.0.0:".$port,$errno,$errstr);
   }
   catch(\Exception $e){
    $this->socket = null;
    $this->log->log("Selhalo vytvareni tcp serveru!");
    return false;
   }
  }
  return true;
 }
 private function clientConnect($client_socket){
  $client_handler = new \Client\Handler($client_socket,$this->vhost_storige,$this->error_handler,$this->clients,$this->config,$this->log);
  $client_handler->start();
  $client_handler->detach();
  $client_handler = null;
 }
 public function handleClients(){
  while(1){
   $read_streams = $this->socket;
   $read_streams[] = $this->console->getStream();
   $write_socket = array();
   $except_socket = array();
   @\stream_select($read_streams, $write_socket, $except_socket, null);
   foreach($read_streams as $stream){
    if($stream == $this->console->getStream()){
     $this->console->handleCommand();
     if($this->console->stopCommand()){
      return;
     }
     $stream = null;
     continue;
    }
    try{
     $client_socket = \stream_socket_accept($stream);
	}
    catch(\Exception $e){
     $this->log->log("Handle new client failed!");
     $stream = null;
	 continue;
    }
    $this->clientConnect($client_socket);
    $client_socket = null;
    $stream = null;
   }
   $read_streams = null;
  }
 }
 public function getLog(){
  return $this->log;
 }
 public function stop(){
  $manipulator = new \Client\Manipulator($this->clients,$this->vhost_storige);
  $manipulator->start();
  $manipulator->join();
  $this->log->log("Exit all Vhosts");
  $this->vhost_storige->removeAll();
  $this->log->log("Exit all Plugins");
  $this->plugin_storige->removeAll();
  if(!is_null($this->socket)){
   foreach($this->socket as $port=>$socker){
    $this->log->log("Shut down server on port: ".$port);
    \stream_socket_shutdown($socker,STREAM_SHUT_RDWR);
   }
   $this->socket = null;
  }
 }
}
