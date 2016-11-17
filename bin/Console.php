<?php
class Console{
 private $stream;
 private $stop;
 private $clients;
 private $vhost_storige;
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
 public function __construct(\Threaded $clients,\Util\PluginStorige $vhost_storige){
  $this->stream = fopen('php://stdin', 'r');
  $this->stop = false;
  $this->clients = $clients;
  $this->vhost_storige = $vhost_storige;
 }
 public function getStream(){
  return $this->stream;
 }
 public function handleCommand(){
  $input_string = fgets($this->stream);
  $command = '';
  $params = array();
  $this->parseCommand($input_string,$command,$params);
  if($command == ''){
   return;
  }
  if($command == 'stop'){
   $this->stop = true;
   return;
  }
  if($command == 'vhost'){
   if(count($params) >= 2){
    if($params[1] == 'list'){
     echo "Vhosts list:\n";
     foreach($this->vhost_storige->getAll() as $vhost){
      echo $vhost->getName()."\n";
     }
     return;
    }
   }
   echo "Usage vhost list\n";
   return;
  }
  echo "Command '".$command."' not found\n";
 }
 public function stopCommand(){
  return $this->stop;
 }
}