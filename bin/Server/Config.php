<?php
namespace Server;
class Config{
 private $data;
 private $default_value = array(
  "ports" => array(7505,7506),
  "max_connection" => 100000,
  "keep_alive_connection_time_out" => 20,
  "directory_file_list" => false,
  "directory_index" => "index.html",
  "vhost_self_select" => false,
 );
 public function __construct(){
  $this->data = array(
   "directory_file_list" => true,
   "directory_index" => "ahoj.html",
   "vhost_self_select" => true,
  );
 }
 public function exists($key){
  return (\array_key_exists($key,$this->data) || \array_key_exists($key,$this->default_value));
 }
 public function get($key){
  if(array_key_exists($key,$this->data)){
   return $this->data[$key];
  }
  if(array_key_exists($key,$this->default_value)){
   return $this->default_value[$key];
  }
  return null;
 }
}