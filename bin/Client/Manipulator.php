<?php
namespace Client;
class Manipulator extends \Thread{
 private $clients;
 private $vhost_storige;
 public function __construct(\Threaded $clients,\Util\PluginStorige $vhost_storige){
  $this->clients = $clients;
  $this->vhost_storige = $vhost_storige;
 }
 public function run(){
  foreach($this->clients as $client){
   \stream_socket_shutdown($client,STREAM_SHUT_RDWR);
   $client = null;
  }
 }
}