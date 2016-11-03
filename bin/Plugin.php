<?php
abstract class Plugin{
 private $data_dir;
 private $log;
 private $server_config;
 final public function __construct(\Server\Config $config,\Util\Log $log,$data_dir){
  $this->data_dir = $data_dir."/".$this->getName();
  if(!is_dir($this->data_dir)){
   mkdir($this->data_dir,0755);
  }
  $this->server_config = $config;
  $this->log = $log;
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
 final public function getName(){
  return \preg_replace("/\\\\.*$/","",\get_class($this));
 }
 abstract public function onLoad();
 abstract public function onExit();
}