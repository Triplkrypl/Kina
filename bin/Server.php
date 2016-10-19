<?php
class Server{
 private $port;
 private $log;
 private $socket;
 private $console;
 private $max_connection;
 private $clients;
 private $vhost_storige;
 private $config;
 public function __construct(\Server\Config $config,\Util\ClassLoader $class_loader,$data_dir){
  $this->config = $config;
  $this->port = $this->config->get("port");
  $this->max_connection = $this->config->get("max_connection");
  $this->log = new \Util\Log();
  $this->vhost_storige = new \Util\PluginStorige($config,$this->log,$class_loader,$data_dir,\Util\ClassLoader::TYPE_VHOST);
  $this->socket = null;
  $this->clients = new \Threaded();
  $this->console = new \Console($this->clients,$this->vhost_storige);
 }
 public function loadVhosts(){
  //$this->vhost_storige->loadAll();
  $this->vhost_storige->load('Base');
  if(is_null($this->vhost_storige->getDefault())){
   $this->log->log("Nepodarilo se najit defaultni vhost!");
   return false;
  }
  return true;
 }
 public function run(){
  $this->log->log("Starting server on port: ".$this->port);
  if(!$this->tcpInit()){
   $this->socket = null;
   $this->log->log("Selhalo vytvareni tcp serveru!");
   return false;
  }
  return true;
 }
 private function tcpInit(){
  $errno = null;
  $errstr = null;
  $this->socket = \stream_socket_server("tcp://0.0.0.0:".$this->port,$errno,$errstr);
  if($this->socket === false){
   return false;
  }
  return true;
 }
 private function clientConnect($client_socket){
  $client_handler = new \Client\Handler($client_socket,$this->vhost_storige,$this->clients,$this->config);
  $client_handler->start();
  $client_handler->detach();
  $client_handler = null;
 }
 public function handleClients(){
  while(1){
   $read_streams = array($this->socket,$this->console->getStream());
   $write_socket = array();
   $except_socket = array();
   if(@\stream_select($read_streams, $write_socket, $except_socket, null) === false){
    return;
   }
   foreach($read_streams as $stream){
    if($stream == $this->console->getStream()){
     $this->console->handleCommand();
     if($this->console->stopCommand()){
      return;
     }
     $stream = null;
     continue;
    }
    if($stream == $this->socket){
     $client_socket = @\stream_socket_accept($this->socket);
     if($client_socket === false){
      $this->log->log("Selhano zpravovani noveho klienta!");
      $stream = null;
      continue;
     }
     $this->clientConnect($client_socket);
     $client_socket = null;
     $stream = null;
     continue;
    }
    $stream = null;
   }
   $read_streams = null;
  }
 }
 public function getLog(){
  return $this->log;
 }
 public function stop(){
  var_dump($this->clients);
  foreach($this->clients as $client){
   /*$client_object = new \Client\Client($client);
   foreach($this->vhost_storige->getAll() as $vhost){
    $vhost->onClientDisconnect($client_object);
    $vhost = null;
   }*/
   //\socket_close($client);
  // var_dump("kiknuti: ",$client_object);
   $client = null;
   //$client_object = null;
  }
  foreach($this->vhost_storige->getAll() as $vhost){
   $this->vhost_storige->remove($vhost->getName());
   $vhost = null;
  }
  if(!is_null($this->socket)){
   $this->log->log("Vypinam server na portu: ".$this->port);
   \stream_socket_shutdown($this->socket,STREAM_SHUT_RDWR);
   $this->socket = null;
  }
 }
}
