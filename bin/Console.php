<?php
class Console extends \Threaded{
 private $stream;
 private $stop;
 private $vhost_storige;
 private $plugin_storige;
 private $config;
 private $commands;
 private $commands_by_plugins;
 protected function parseCommand($text,&$command,&$params){
  $text = \str_replace("\n","",$text);
  $array = \explode(' ',$text);
  $command = $array[0];
  foreach($array as $param){
   if($param == ''){
    continue;
   }
   $params[] = $param;
  }
 }
 protected function stop($command,array $params,$string){
  $this->stop = true;
 }
 protected function vhosts($command,array $params,$string){
  echo "Vhosts list:\n";
  foreach($this->vhost_storige->getAll() as $vhost){
   echo " ".$vhost->getName()."\n";
  }
 }
 protected function plugins($command,array $params,$string){
  echo "Plugins list:\n";
  foreach($this->plugin_storige->getAll() as $plugin){
   echo " ".$plugin->getName()."\n";
  }
 }
 protected function cmdList($command,array $params,$string){
  echo "Console commands list:\n";
  foreach($this->commands as $command){
   echo " ".$command->getname().(($command->getDescription() != "") ? ": ".$command->getDescription() : "")."\n";
  }
 }
 public function __construct(\Server\Config $config){
  $this->stream = fopen('php://stdin', 'r');
  $this->stop = false;
  $this->vhost_storige = null;
  $this->plugin_storige = null;
  $this->config = $config;
  $this->commands_by_plugins = array();
  $this->commands = array();
  $this->addCommand(new \Console\Command("stop",$this,"stop","Shutdown server aplication!"));
  $this->addCommand(new \Console\Command("vhosts",$this,"vhosts","Show list of loaded Vhosts"));
  $this->addCommand(new \Console\Command("plugins",$this,"plugins","Show list of loaded Plugins"));
  $this->addCommand(new \Console\Command("?",$this,"cmdList","Show list of all commands"));
 }
 public function setStoriges(\Util\PluginStorige $vhost_storige,\Util\PluginStorige $plugin_storige){
  $this->plugin_storige = $plugin_storige;
  $this->vhost_storige = $vhost_storige;
 }
 public function getStream(){
  return $this->stream;
 }
 public function addCommand(\Console\Command $command,$plugin_name = ""){
  $this->lock();
  $commands = $this->commands;
  $commands_by_plugins = $this->commands_by_plugins;
  if(array_key_exists($command->getName(),$commands)){
   $this->unlock();
   throw new \Exception("Console command '".$command->getName()."' allready exists");
  }
  $commands[$command->getName()] = $command;
  $commands_by_plugins[$plugin_name][] = $command->getName();
  $this->commands = $commands;
  $this->commands_by_plugins = $commands_by_plugins;
  $this->unlock();
 }
 public function removeCommand($command_name,$plugin_name = ""){
  $this->lock();
  $commands = $this->commands;
  $commands_by_plugins = $this->commands_by_plugins;
  if(!array_key_exists($command_name,$commands)){
   $this->unlock();
   throw new \Exception("Console command '".$command->getName()."' not found");
  }
  $keys = array_keys($commands_by_plugins[$plugin_name],$command_name);
  if(count($keys) == 0){
   $this->unlock();
   throw new \Exception("Console command '".$command->getName()."' not belong plugin '".$plugin_name."'");
  }
  $key = $keys[0];
  unset($commands[$command_name]);
  unset($commands_by_plugins[$plugin_name][$key]);
  $this->commands = $commands;
  $this->commands_by_plugins = $commands_by_plugins;
  $this->unlock();
 }
 public function removeAllCommands($plugin_name){
  $this->lock();
  $commands_by_plugins = $this->commands_by_plugins;
  if(!array_key_exists($plugin_name,$commands_by_plugins)){
   $this->unlock();
   return;
  }
  $commands = $this->commands;
  foreach($commands_by_plugins[$plugin_name] as $command_name){
   unset($commands[$command_name]);
  }
  $this->commands = $commands;
  unset($commands_by_plugins[$plugin_name]);
  $this->commands_by_plugins = $commands_by_plugins;
  $this->unlock();
 }
 public function handleCommand(){
  $input_string = fgets($this->stream);
  if($this->config->get("console") == false){
   return;
  }
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