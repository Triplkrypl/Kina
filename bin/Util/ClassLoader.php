<?php
//prepsat v host na plugin
namespace Util;
class ClassLoader{
 const TYPE_PLUGIN = 'plugin';
 const TYPE_VHOST = 'vhost';
 private static $base_dir;
 private $plugin_base_dir = array();
 private static $init = false;
 private static $plugin_classes = array();
 private $plugin_hash;
 private static $plugin_thread_hash = array();
 private static function disableCompetitor(){
  $competitors = spl_autoload_functions();
  foreach($competitors as $autoloader){
   if(is_array($autoloader)){
    if($autoloader[0] == \get_called_class() && $autoloader[1] == "loadClass"){
     continue;
    }
   }
   spl_autoload_unregister($autoloader);
  }
 }
 public static function init($base_dir){
  if(static::$init == false){
   static::$base_dir = $base_dir;
   static::$init = true;
   static::$plugin_classes = array(
    static::TYPE_VHOST => array(),
   );
  }
 }
 public static function loadClass($class_name){
  $file_name = str_replace("\\","/",$class_name);
  $file_name = static::$base_dir."/".$file_name.".php";
  if(file_exists($file_name)){
   require_once $file_name;
  }
  static::disableCompetitor();
 }
 private function addPluginClass($type,$plugin_name,$class_name,$file_name){
  //------------vic validace pro clasu, soubor musi obsahovat jednu clasu a musi sedet name space
  //------------clasa nesmi obsahovat vlastnosti jako nadrazena klasa
  if(@runkit_lint_file($this->plugin_base_dir[$type]."/".$file_name) == false){
   throw new \Exception("Class: ".$class_name." have sintax error!");
  }
  runkit_import($this->plugin_base_dir[$type]."/".$file_name, (RUNKIT_IMPORT_OVERRIDE | RUNKIT_IMPORT_CLASSES) & ~RUNKIT_IMPORT_CLASS_STATIC_PROPS & ~RUNKIT_IMPORT_FUNCTIONS & ~RUNKIT_IMPORT_CLASS_CONSTS);
  static::$plugin_classes[$type][$plugin_name][] = $class_name;
 }
 private function loadPluginNamespace($type,$plugin_name,$name_space,$dir_name){
  if(is_dir($this->plugin_base_dir[$type]."/".$dir_name)){
   $dir = \opendir($this->plugin_base_dir[$type]."/".$dir_name);
   while(false !== ($file_name = readdir($dir))){
    if($file_name == "." || $file_name == ".."){
     continue;
    }
    if(is_dir($this->plugin_base_dir[$type]."/".$dir_name."/".$file_name)){
     $this->loadPluginNamespace($type,$plugin_name,$name_space."\\".$file_name,$dir_name."/".$file_name);
     continue;
    }
    if(is_file($this->plugin_base_dir[$type]."/".$dir_name."/".$file_name)){
     if(preg_match("/.php$/",$file_name)){
      $this->addPluginClass($type,$plugin_name,$name_space."\\".str_replace(".php","",$file_name),$dir_name."/".$file_name);
     }
    }
   }
   \closedir($dir);
  }
 }
 private function generatePluginHash($type,$plugin_name){
  $hash = rand(0,200000000);
  if(!property_exists($this->plugin_hash,$type.'_'.$plugin_name)){
   return $hash;
  }
  if($this->plugin_hash[$type.'_'.$plugin_name] == $hash){
   return generateVhostHash($type,$plugin_name);
  }
  return $hash;
 }
 public function __construct($vhost_base_dir){
  $this->plugin_hash = new \Threaded();
  $this->plugin_base_dir[static::TYPE_VHOST] = $vhost_base_dir;
 }
 public function checkPlugin($type,$plugin_name){
  if(!is_array(static::$plugin_thread_hash)){
   static::$plugin_thread_hash = array();
   foreach($this->plugin_hash as $key=>$plugin_hash){
    static::$plugin_thread_hash[$key] = $plugin_hash;
    $key = null; $plugin_hash = null;
   }
  }
  if(array_key_exists($type.'_'.$plugin_name,static::$plugin_thread_hash)){
   if(static::$plugin_thread_hash[$type.'_'.$plugin_name] == $this->plugin_hash[$type.'_'.$plugin_name]){
	return true;
   }
  }
  return false;
 }
 public function getPluginBaseDir($type){
  return $this->plugin_base_dir[$type];
 }
 public function loadPluginClass($type,$pugin_name,$main = false){
  if(\class_exists("Vhost\\Vhost") == false){
   static::loadClass("Vhost\\Vhost");
  }
  $this->addPluginClass($type,$pugin_name,$pugin_name,$pugin_name.".php");
  static::loadPluginNamespace($type,$pugin_name,$pugin_name,$pugin_name);
  if($main){
   $plugin_hash = $this->generatePluginHash($type,$pugin_name);
   $this->plugin_hash[$type.'_'.$pugin_name] = $plugin_hash;
  }
  static::$plugin_thread_hash[$type.'_'.$pugin_name] = $this->plugin_hash[$type.'_'.$pugin_name];
 }
 public function removePluginClass($type,$pugin_name){
  unset($this->plugin_hash[$type.'_'.$pugin_name]);
  unset(static::$plugin_thread_hash[$type.'_'.$pugin_name]);
  foreach(static::$plugin_classes[$type][$pugin_name] as $class){
   $reflection = new \ReflectionClass($class);
   echo "Smazani clasy: ".$class."\n";
   while(1){
    foreach($reflection->getProperties () as $property=>$val){
     @runkit_default_property_remove($class,$val->getName());
    }
    $reflection = $reflection->getParentClass();
    if(!$reflection){
	 break;
	}
   }
  }
  unset(static::$plugin_classes[$type][$pugin_name]);
 }
}