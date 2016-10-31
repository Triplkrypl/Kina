<?php
namespace Server;
class Config{
 private $data;
 private $default_value;
 public function __construct(){
  $this->default_value = array(
   "ports" => array(80),
   "max_connection" => 100000,
   "keep_alive_connection_time_out" => 20,
   "directory_file_list" => false,
   "directory_index" => "index.html",
   "vhost_select" => "config",
   "vhost_map" => array(),
  );
  $this->data = array(
   "ports" => array(7505,7506),
   "directory_file_list" => true,
   "directory_index" => "ahoj.html",
   "vhost_select" => "self",
   "vhost_map" => array("/^game.realhys.cz/" => "GameRealhysCz"),
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
  throw new \Exception("Server config value not found key: ".$key);
 }
}