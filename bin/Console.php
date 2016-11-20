<?php
class Console{
 private $stream;
 private $stop;
 private $vhost_storige;
 private $commands;
 private $commands_by_plugins;
 private function parseCommand($text,&$command,&$params){
  $text = \str_replace("\n","",$text);
  $array = \explode(' ',$text);
  \reset($array);
  $command = $array[\key($array)];
  foreach($array as $param){
   if($param == ''){
    continue;
   }
   $params[] = $param;
  }
 }
 private function stop($command,array $params,$string){
  $this->stop = true;
 }
 private function vhosts($command,array $params,$string){
  echo "Vhosts list:\n";
  foreach($this->vhost_storige->getAll() as $vhost){
   echo " ".$vhost->getName()."\n";
  }
 }
 private function cmdList($command,array $params,$string){
  echo "Console commands list:\n";
  foreach($this->commands as $command){
   echo " ".$command->getname()." ".$command->getDescription()."\n";
  }
 }
 public function __construct(\Util\PluginStorige $vhost_storige,\Util\PluginStorige $plugin_storige){
  $this->stream = fopen('php://stdin', 'r');
  $this->stop = false;
  $this->vhost_storige = $vhost_storige;
  $this->commands_by_plugins = array();
  $this->commands = array();
  $this->addCommand(new \Console\Command("stop",array($this,"stop")));
  $this->addCommand(new \Console\Command("vhosts",array($this,"vhosts")));
  $this->addCommand(new \Console\Command("?",array($this,"cmdList")));
 }
 public function getStream(){
  return $this->stream;
 }
 public function addCommand($command,$plugin_name = ""){
  if(array_key_exists($command->getName(),$this->commands)){
   throw new \Exception("Console command '".$command->getName()."' allready exists");
  }
  $this->commands[$command->getName()] = $command;
  $this->commands_by_plugins[$plugin_name][] = $command->getName();
 }
 public function removeCommand($command_name,$plugin_name = ""){
  if(!array_key_exists($command_name,$this->commands)){
   throw new \Exception("Console command '".$command->getName()."' not found");
  }
  $keys = array_keys($this->commands_by_plugins[$plugin_name],$command_name);
  if(count($keys) == 0){
   throw new \Exception("Console command '".$command->getName()."' not belong plugin '".$plugin_name."'");
  }
  $key = $keys[0];
  unset($this->commands[$command_name]);
  unset($this->commands_by_plugins[$plugin_name][$key]);
 }
 public function removeAllCommands($plugin_name){
  if(!array_key_exists($plugin_name,$this->commands_by_plugins)){
   return;
  }
  foreach($this->commands_by_plugins[$plugin_name] as $command_name){
   unset($this->commands[$command_name]);
  }
  unset($this->commands_by_plugins[$plugin_name]);
 }
 public function handleCommand(){
  $input_string = fgets($this->stream);
  $command = '';
  $params = array();
  $this->parseCommand($input_string,$command,$params);
  if($command == ''){
   return;
  }
  foreach($this->commands as $objects_command){
   if($objects_command->getName() == $command){
    call_user_func($objects_command->getCallback(),$command,$params,$input_string);
	return;
   }
  }
  echo "Command '".$command."' not found use '?' for list available commands\n";
 }
 public function stopCommand(){
  return $this->stop;
 }
}