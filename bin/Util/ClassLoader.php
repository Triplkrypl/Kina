<?php
namespace Util;
class ClassLoader{
 private $base_dir;
 private $plugin_base_dir;
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
 public function __construct($base_dir,$plugin_base_dirs){
  $this->base_dir = $base_dir;
  $this->plugin_base_dir = $plugin_base_dirs;
 }
 public function register(){
  \spl_autoload_register(array($this,"loadClass"));
 }
 public function getPluginBaseDir(){
  return $this->plugin_base_dir;
 }
}