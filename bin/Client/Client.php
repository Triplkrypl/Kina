<?php
namespace Client;
class Client{
 private $ip;
 private $port;
 public function __construct($socket){
  $name = \explode(":",\stream_socket_get_name($socket,true));
  $this->ip = $name[0];
  $this->port = $name[1];
 }
 public function getIp(){
  return $this->ip;
 }
 public function getPort(){
  return $this->port;
 }
}