<?php
namespace Client;
class Manipulator extends \Thread{
 private $clients;
 public function __construct(\Threaded $clients){
  $this->clients = $clients;
 }
 public function run(){
  $this->clients->lock();
  foreach($this->clients as $client){
   \stream_socket_shutdown($client,STREAM_SHUT_RDWR);
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