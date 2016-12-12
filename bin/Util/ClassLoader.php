<?php
namespace Util;
class ClassLoader{
 private $base_dir;
 private $plugin_base_dir;
 private $in_thread;
 private $in_thread_loaded_class;
 private function disableCompetitor(){
  $competitors = spl_autoload_functions();
  foreach($competitors as $autoloader){
   if(is_array($autoloader)){
    if($autoloader[0] == $this && $autoloader[1] == "loadClass"){
     continue;
    }
   }
   \spl_autoload_unregister($autoloader);
  }
 }
 private function loadFile($class_name,$file){
  $output = array();
  $return = 0;
  \exec("php -l ".$file." 2>&1",$output,$return);
  if($return != 0){
   $error_message = "";
   foreach($output as $row){
    $error_message .= $row." ";
   }
   throw new \Exception($error_message);
  }
  require_once $file;
  if(!\class_exists($class_name)){
   throw new \Exception("Class: ".$class_name." not define in file: ".$file);
  }
  if($this->in_thread){
   $this->in_thread_loaded_class->lock();
   $found = false;
   foreach($this->in_thread_loaded_class as $value){
    if($value == $class_name){
     $found = true;
	 break;
	}
   }
   if(!$found){
    $this->in_thread_loaded_class[] = $class_name;
   }
   $this->in_thread_loaded_class->unlock();
  }
 }
 public function __construct($base_dir,$plugin_base_dirs){
  $this->base_dir = $base_dir;
  $this->plugin_base_dir = $plugin_base_dirs;
  $this->in_thread = false;
  $this->in_thread_loaded_class = new \Threaded();
 }
 public function loadInMainThreadClassFromChildrens(){
  if(!$this->in_thread){
   $this->in_thread_loaded_class->lock();
   foreach($this->in_thread_loaded_class as $key=>$class_name){
    $this->loadClass($class_name);
    unset($this->in_thread_loaded_class[$key]);
   }
   $this->in_thread_loaded_class->unlock();
  }
 }
 public function loadClass($class_name){
  $this->disableCompetitor();
  $file_name = str_replace("\\","/",$class_name).".php";
  $full_file_name = $this->base_dir."/".$file_name;
  if(is_file($full_file_name)){
   $this->loadFile($class_name,$full_file_name);
   return;
  }
  $full_file_name = $this->plugin_base_dir."/".$file_name;
  if(is_file($full_file_name)){
   $this->loadFile($class_name,$full_file_name);
   return;
  }
  throw new \Exception("File not found for class: ".$class_name);
 }
 public function register($in_thread = false){
  $this->in_thread = $in_thread;
  \spl_autoload_register(array($this,"loadClass"));
 }
 public function getPluginBaseDir(){
  return $this->plugin_base_dir;
 }
}