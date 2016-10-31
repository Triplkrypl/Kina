<?php
namespace Server\Request;
class Host{
 private $host_string;
 private $host;
 private $port;
 public function __construct($host_string){
  $this->host_string = $host_string;
  $host_array = \explode(":",$host_string);
  $this->host = $host_array[0];
  $this->port = null;
  if(array_key_exists(1,$host_array)){
   $this->port = $host_array[1];
  }
 }
 public function getHost(){
  return $this->host;
 }
 public function getPort(){
  return $this->port;
 }
 public function getRawHost(){
  return $this->host_string;
 }
}