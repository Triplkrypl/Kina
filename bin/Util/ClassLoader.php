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
 private function loadFile($file){
  require_once $file;
  return;
 }
 public function loadClass($class_name){
  $file_name = str_replace("\\","/",$class_name).".php";
  $full_file_name = $this->base_dir."/".$file_name;
  if(is_file($full_file_name)){
   $this->loadFile($full_file_name);
  }
  $full_file_name = $this->plugin_base_dir."/".$file_name;
  if(is_file($full_file_name)){
   $this->loadFile($full_file_name);
  }
  $this->disableCompetitor();
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