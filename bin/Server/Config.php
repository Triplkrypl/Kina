<?php
namespace Server;
class Config{
 private $data;
 private $default_value;
 private $value_types;
 private $loaded;
 private function load($file){
  if(!is_file($file)){
   $default_value = $this->default_value;
   $default_value["vhost_map"] = new \StdClass();
   \file_put_contents($file,\json_encode($default_value,JSON_PRETTY_PRINT));
   return array();
  }
  $data = \file_get_contents($file);
  $data = \json_decode($data,true);
  if(!is_array($data)){
   throw new \Exception("Config not contain valid main JSON object");
  }
  foreach($data as $key=>$val){
   if(!is_string($key)){
    throw new \Exception("Config not contain valid main JSON object");
   }
   if(strlen($key) == 0 || $key[0] == "#"){
    unset($data[$key]);
    continue;
   }
   if(array_key_exists($key,$this->value_types)){
    $this->validateKey($key,$this->value_types[$key],$val);
   }
  }
  return $data;
 }
 private function validateKey($key,$type,$val){
  if(is_array($type)){
   if(!is_array($val)){
    throw new \Exception("Config key: ".$key," is not ".$type[0]);
   }
   foreach($val as $next_key=>$next_val){
    if(
     (is_string($next_key) && $type[0] == "array") ||
     (is_int($next_key) && $type[0] == "object")
	){
	 throw new \Exception("Config key: ".$key," is not ".$type[0]);
	}
    $next_key = ($type[0] == "array") ? $key."[".$next_key."]" : $key."->".$next_key;
    $this->validateKey($next_key,$type[1],$next_val);
   }
   return;
  }
  $val_function = "is_".$type;
  if(!$val_function($val)){
   throw new \Exception("Config key: ".$key." is not ".$type);
  }
 }
 public function __construct($file = null){
  $this->loaded = false;
  $this->default_value = array(
   "#" => "Key start with '#' is comment",
   "#ports" => "List of listen TCP ports, default value [80]",
   "ports" => array(80),
   "#max_connection" => "Maximum TCP connection per listen port, default value 100000",
   "max_connection" => 100000,
   "#keep_alive_connection_time_out" => "Time for client how long he will keep TCP connection open (in minutes), default value 20",
   "keep_alive_connection_time_out" => 20,
   "#directory_file_list" => "If is request for directory default method 'onNoPhpRequest' will return on true value list of file and directory, default value false",
   "directory_file_list" => false,
   "#directory_index" => "If is request for directory and directory_file_list is false default method 'onNoPhpRequest' try load content of file directory_index, default value index.html",
   "directory_index" => "index.html",
   "#vhost_select0" => "Setup selection method for vhost, values config self auto, default value config",
   "#vhost_select1" => "config will select vhost by vhost_map kay",
   "#vhost_select2" => "auto will transform host name on vhost name example kina.cloud handle vhost KinaCloud",
   "#vhost_select3" => "self will call vhost method 'onVhostChoise' if vhost handle request",
   "vhost_select" => "config",
   "#vhost_map" => "Object with regex rules for vhots selection, property name is regex and value is vhost name, default value {}",
   "vhost_map" => array(),
   "#console" => "Setup loging messages to console and enable or disable console input, default value true",
   "console" => true,
  );
  $this->value_types = array(
   "ports" => array("array","int"),
   "max_connection" => "int",
   "keep_alive_connection_time_out" => "int",
   "directory_file_list" => "bool",
   "directory_index" => "string",
   "vhost_select" => "string",
   "vhost_map" => array("object","string"),
   "console" => "bool",
   "default_timezone" => "string",
  );
  $this->data = array();
  if(is_null($file)){
   return;
  }
  $this->data = $this->load($file);
  $this->loaded = true;
 }
 public function exists($key){
  return (\array_key_exists($key,$this->data) || \array_key_exists($key,$this->default_value));
 }
 public function loaded(){
  return $this->loaded;
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