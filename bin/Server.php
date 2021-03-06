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
 public function __construct($config_file,\Util\ClassLoader $class_loader,\Util\ErrorHandler $error_handler,$data_dir,$log_dir){
  $create_config_exception = null;
  $this->config = null;
  try{
   $this->config = new \Server\Config($config_file);
  }
  catch(\Exception $e){
   $create_config_exception = $e;
   $this->config = new \Server\Config();
  }
  try{
   date_default_timezone_set($this->config->get("default_timezone"));
  }
  catch(\Exception $e){
  }
  $this->log = new \Util\Log($this->config,$log_dir);
  if(!is_null($create_config_exception)){
   $this->log->log("Failed loading configurations ".$create_config_exception->getMessage(),"error");
  }
  $this->ports = $this->config->get("ports");
  $this->max_connection = $this->config->get("max_connection");
  $this->error_handler = $error_handler;
  $this->console = new \Console($this->log);
  $this->plugin_storige = new \Util\PluginStorige($this->config,$this->log,$class_loader,$data_dir,\Util\PluginStorige::TYPE_PLUGIN,$this->console);
  $this->vhost_storige = new \Util\PluginStorige($this->config,$this->log,$class_loader,$data_dir,\Util\PluginStorige::TYPE_VHOST,$this->console,$this->plugin_storige);
  $this->socket = null;
  $this->clients = new \Threaded();
 }
 public function isLoadedConfig(){
  return $this->config->loaded();
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
  $this->console->setStoriges($this->vhost_storige,$this->plugin_storige);
  if(count($this->ports) == 0){
   $this->log->log("Lisener ports are not set","error");
   return false;
  }
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
    $this->log->log("Failed starting server on port: ".$port,"warning");
   }
  }
  if(count($this->socket) == 0){
   $this->log->log("None lisener are successfull started","error");
   return false;
  }
  return true;
 }
 private function clientConnect($client_socket){
  $this->plugin_storige->getClassLoader()->loadInMainThreadClassFromChildrens();
  $client_handler = new \Client\Handler($client_socket,$this->vhost_storige,$this->error_handler,$this->clients,$this->config,$this->log);
  $client_handler->start();
  $client_handler->detach();
  $client_handler = null;
 }
 public function handleClients(){
  while(1){
   $read_streams = $this->socket;
   if($this->config->get("console")){
    $read_streams[] = $this->console->getStream();
   }
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
     $this->log->log("Handle new client failed","warning");
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
  $manipulator = new \Client\Manipulator($this->clients,$this->error_handler);
  $manipulator->start();
  $manipulator->join();
  $this->log->log("Exit all Vhosts");
  $this->vhost_storige->removeAll();
  $this->log->log("Exit all Plugins");
  $this->plugin_storige->removeAll();
  if(!is_null($this->socket)){
   foreach($this->socket as $port=>$socket){
    $this->log->log("Shut down server on port: ".$port);
    \stream_socket_shutdown($socket,STREAM_SHUT_RDWR);
    \fclose($socket);
   }
   $this->socket = null;
  }
 }
}
