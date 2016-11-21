<?php
abstract class Plugin extends \Threaded{
 private $data_dir;
 private $log;
 private $server_config;
 private $console;
 final public function __construct(\Server\Config $config,\Util\Log $log,\Console $console,$data_dir){
  $this->data_dir = $data_dir."/".$this->getName();
  if(!is_dir($this->data_dir)){
   mkdir($this->data_dir,0755);
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
 }
 final public function __destruct(){
 }
 final protected function serverLog($message,$log_type = ""){
  $this->log->log($message,$log_type,$this->getName());
 }
 final protected function serverLogException($add_message,\Exception $e,$log_type = ""){
  $this->log->logException($add_message,$e,$log_type,$this->getName());
 }
 final protected function getServerConfig(){
  return $this->server_config;
 }
 final protected function getDataDir(){
  return $this->data_dir;
 }
 final protected function registerCommand(\Console\Command $command){
  if(is_null($command->getCallbackObject())){
   $command->setCallbackObject($this);
  }
  $console = $this->console;
  $console->addCommand($command,$this->getName());
 }
 final protected function removeCommand($command_name){
  $this->console->removeCommand($command_name,$this->getName());
 }
 final public function getName(){
  return \preg_replace("/\\\\.*$/","",\get_class($this));
 }
 public function getDependence(){
  return array();
 }
 public function getVersion(){
  return null;
 }
 public function onLoad(){}
 public function onExit(){}
 public function onConsoleCommand($command,array $params,$raw_string){}
}