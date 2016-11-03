<?php
namespace Util;
class PluginStorige{
 const TYPE_PLUGIN = 'Plugin';
 const TYPE_VHOST = 'Vhost';
 private $data;
 private $data_dir;
 private $config;
 private $log;
 private $class_loader;
 private $type;
 private function getAllPluginName(){
  $plugin_base_dir = $this->class_loader->getPluginBaseDir($this->type);
  $dir = \opendir($plugin_base_dir);
  $names = array();
  while($dir_name = readdir($dir)){
   if($dir_name == "." || $dir_name == ".."){
    continue;
   }
   if(\preg_match("/^[A-Z]{1}[a-zA-Z0-9]*\$/",$dir_name) == false || is_file($plugin_base_dir."/".$dir_name)){
    continue;
   }
   if(!is_file($this->class_loader->getPluginBaseDir($this->type)."/".$dir_name."/".$this->type.".php")){
    continue;
   }
   $names[] = $dir_name;
  }
  return $names;
 }
 private function remove(\Plugin $plugin){
  $this->log->log("Exiting plugin: ".$plugin->getName());
  try{
   $plugin->onExit();
  }
  catch(\Exception $e){
   $this->log->logException("Plugin trow exception onExiting",$e,"warning");
  }
  unset($this->data[$plugin->getName()]);
 }
 private function load($plugin_name){
  $this->log->log("Loading Plugin: ".$plugin_name);
  $main_plugin_class = $plugin_name."\\".$this->type;
  if(array_key_exists($plugin_name,$this->data)){
   return true;
  }
  try{
   $plugin = new $main_plugin_class($this->config,$this->log,$this->data_dir);
  }
  catch(\Exception $e){
   $this->log->logException("Plugin: ".$plugin_name." trow exception while creating",$e,"warning");
   return false;
  }
  if(get_parent_class($main_plugin_class) != $this->type){
   $this->log->log("Plugin not found, class: ".$main_plugin_class." not extend class: ".$this->type,"warning");
   return false;
  }
  try{
   $plugin->onLoad();
  }
  catch(\Exception $e){
   $this->log->logException("Plugin trow exception onLoading",$e,"warning");
   return false;
  }
  $this->data[$plugin_name] = $plugin;
  return true;
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
  $plugin_names = $this->getAllPluginName();
  foreach($plugin_names as $plugin_name){
   $this->load($plugin_name);
   $plugin_name = null;
  }
 }
 public function removeAll(){
  foreach($this->data as $plugin){
   $this->remove($plugin);
  }
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
 public function getBase(){
  return $this->get("Base");
 }
 public function getClassLoader(){
  return $this->class_loader;
 }
}