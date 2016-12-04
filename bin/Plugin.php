<?php
abstract class Plugin extends \Threaded{
 private $data_dir;
 private $log;
 private $server_config;
 private $console;
 private $plugin_storige;
 final public function __construct(\Server\Config $config,\Util\Log $log,\Console $console,\Util\PluginStorige $plugin_storige,$data_dir){
  $this->data_dir = $data_dir."/".$this->getName();
  if(!is_dir($this->data_dir)){
   mkdir($this->data_dir,0755);
   if(\get_parent_class($this) == "Vhost"){
    mkdir($this->data_dir."/static",0755);
   }
  }
  $version = $this->getVersion();
  if(!is_null($version)){
   if(\Util\Convert::checkVersionFormat($version) == false){
    throw new \Exception("Plugin version '".$version."' have wrong format example '0.0.0'");
   }
  }
  $this->server_config = $config;
  $this->log = $log;
  $this->console = $console;
  $this->plugin_storige = $plugin_storige;
 }
 final public function __destruct(){
 }
 /**
  * @param string $message
  * @param string $log_type
  */
 final protected function serverLog($message,$log_type = ""){
  $this->log->log($message,$log_type,$this->getName());
 }
 /**
  * @param string $add_message
  * @param \Exception $e
  * @param string $log_type
  */
 final protected function serverLogException($add_message,\Exception $e,$log_type = ""){
  $this->log->logException($add_message,$e,$log_type,$this->getName());
 }
 /**
  * @return \Server\Config
  */
 final protected function getServerConfig(){
  return $this->server_config;
 }
 /**
  * @return string
  */
 final protected function getDataDir(){
  return $this->data_dir;
 }
 /**
  * @param \Console\Command $command
  */
 final protected function registerCommand(\Console\Command $command){
  if(is_null($command->getCallbackObject())){
   $command->setCallbackObject($this);
  }
  $console = $this->console;
  $console->addCommand($command,$this->getName());
 }
 /**
  * @param string $command_name
  */
 final protected function removeCommand($command_name){
  $this->console->removeCommand($command_name,$this->getName());
 }
 /**
  * @param string $plugin_name
  * @return \Plugin|null
  */
 final protected function getPlugin($plugin_name){
  if($plugin_name == $this->getName()){
   throw new \Exception("Hey man. You try get '".$plugin_name."' plugin from '".$this->getName()."' plugin it is useless, please don't do it i want be clever plugin or Kina can be angry.");
  }
  return $this->plugin_storige->get($plugin_name);
 }
 /**
  * @return string
  */
 final public function getName(){
  return \preg_replace("/\\\\.*$/","",\get_class($this));
 }
 /**
  * @return \Plugin\Dependence[]
  */
 public function getDependence(){
  return array();
 }
 /**
  * @return null|string
  */
 public function getVersion(){
  return null;
 }
 public function onLoad(){}
 public function onExit(){}
 /**
  * @param string $command
  * @param string[] $params
  * @param string $raw_string
  */
 public function onConsoleCommand($command,array $params,$raw_string){}
}