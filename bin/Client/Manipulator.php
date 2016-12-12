<?php
namespace Client;
class Manipulator extends \Thread{
 private $clients;
 private $error_handler;
 public function __construct(\Threaded $clients,\Util\ErrorHandler $error_handler){
  $this->clients = $clients;
  $this->error_handler = $error_handler;
 }
 public function run(){
  $this->error_handler->register();
  $this->clients->lock();
  foreach($this->clients as $key=>$client){
   try{
    \stream_socket_shutdown($client,STREAM_SHUT_RDWR);
   }
   catch(\Exception $e){
    \fclose($client);
    unset($this->clients[$key]);
   }
   $client = null;
  }
  $this->clients->unlock();
  while(true){
   \usleep(100);
   $this->clients->lock();
   $count = count($this->clients);
   $this->clients->unlock();
   if($count == 0){
    break;
   }
  }
 }
}