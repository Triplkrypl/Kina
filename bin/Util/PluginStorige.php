<?php
namespace Util;
class PluginStorige{
 private $data;
 private $data_dir;
 private $config;
 private $log;
 private $class_loader;
 private $type;
 private function getAllPluginName(){
  $plugin_base_dir = $class_loader->getPluginBaseDir($this->type);
  $dir = \opendir($plugin_base_dir);
  $names = array();
  while($file_name = readdir($dir)){
   if($file_name == "." || $file_name == ".." || \preg_match("/^[A-Z]{1}[a-z0-9]\.php$/",$file_name) == false || is_dir($plugin_base_dir."/".$file_name)){
    continue;
   }
   $plugin_name = \str_replace(".php","",$file_name);
   $names[] = $plugin_name;
  }
  return $names;
 }
 public function __construct(\Server\Config $config,\Util\Log $log,\Util\ClassLoader $class_loader,$data_dir,$type){
  $this->data = new \Threaded;
  $this->config = $config;
  $this->data_dir = $data_dir;
  $this->log = $log;
  $this->class_loader = $class_loader;
  $this->type = $type;
 }
 public function loadAll(){
  if(count($this->data) != 0){
   return;
  }
  $plugin_names = getAllPluginName();
  foreach($plugin_names as $plugin_name){
   $this->load($plugin_name);
   $plugin_name = null;
  }
 }
 public function checkAllPlugin(){
  foreach($this->data as $plugin_name=>$plugin){
   if($this->class_loader->checkPlugin($this->type,$plugin_name) == false){
    $this->class_loader->loadPluginClass($this->type,$plugin_name);
   }
   $plugin = null; $plugin_name = null;
  }
 }
 public function load($plugin_name){
  $this->log->log("Loading Plugin: ".$plugin_name);
  $class_loader = $this->class_loader;
  if(!file_exists($class_loader->getPluginBaseDir($this->type)."/".$plugin_name.".php")){
   $this->log->log("Plugin not found, file: ".$class_loader->getPluginBaseDir($this->type)."/".$plugin_name.".php"." not exists");
   $this->remove($plugin_name);
   return false;
  }
  if(array_key_exists($plugin_name,$this->data)){
   return true;
  }
  try{
   $this->class_loader->loadPluginClass($this->type,$plugin_name,true);
  }
  catch(\Exception $e){
   $this->log->log($e->getMessage());
   $this->remove($plugin_name);
   return false;
  }
  $plugin = new $plugin_name($this->config,$this->data_dir);
  $plugin->onLoad();
  $this->data[$plugin_name] = $plugin;
  return true;
 }
 public function remove($plugin_name){
  $this->log->log("Exiting plugin: ".$plugin_name);
  if(array_key_exists($plugin_name,$this->data)){
   $this->data[$plugin_name]->onExit();
   unset($this->data[$plugin_name]);
   $this->class_loader->removePluginClass($this->type,$plugin_name);
   return true;
  }
  return false;
 }
 public function get($plugin_name){
  if(array_key_exists($plugin_name,$this->data)){
   return $this->data[$plugin_name];
  }
  return null;
 }
 public function getAll(){
  return $this->data;
 }
 public function getNotLoadedPluginList(){
  $all_names = $this->getAllPluginName();
  foreach($all_names as $key=>$name){
   if(array_key_exists($name,$this->data)){
    unset($all_names[$key]);
   }
   $key = null; $name = null;
  }
  return $all_names;
 }
 public function getDefault(){
  return $this->get("Base");
 }
}