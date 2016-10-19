<?php
abstract class Plugin{
 private $data_dir;
 private $server_config;
 final public function __construct(\Server\Config $config,$data_dir){
  $this->data_dir = $data_dir."/".$this->getName();
  $this->server_config = $config;
 }
 final public function __destruct(){
 }
 final protected function getServerConfig(){
  return $this->server_config;
 }
 final protected function getDataDir(){
  return $this->data_dir;
 }
 final public function getName(){
  return \get_class($this);
 }
 abstract public function onLoad();
 abstract public function onExit();
}