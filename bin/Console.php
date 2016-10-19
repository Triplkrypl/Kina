<?php
class Console/* extends Threaded*/{
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
  $command = 'clients';
  $params = array();
  $this->parseCommand($input_string,$command,$params);
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
     if(count($params) == 3 && $params[2] == 'all'){
      foreach($this->vhost_storige->getNotLoadedVhostList() as $name){
       echo $name." (not loaded)\n";
      }
     }
     return;
    }
    if($params[1] == 'load'){
     if(count($params) == 2){
      echo "Usage vhost load 'vhost name'\n";
      return;
     }
     if(!is_null($this->vhost_storige->get($params[2]))){
      echo "Vhost: ".$params[2]." allready loaded\n";
      return;    
     }
     $this->vhost_storige->load($params[2]);
     return;
    }
    if($params[1] == 'exit'){
     if(count($params) == 2){
      echo "Usage vhost exit 'vhost name'\n";
      return;
     }
     if(is_null($this->vhost_storige->get($params[2]))){
      echo "Vhost: ".$params[2]." not loaded\n";
      return;
     }
     if($params[2] == "Default"){
      echo "Default vhost can be only reload\n";
      return;
     }
     $this->vhost_storige->remove($params[2]);
     return;
    }
    if($params[1] == "reload"){
     if(count($params) == 2){
      echo "Usage vhost reload 'vhost name'\n";
      return;
     }
     if(is_null($this->vhost_storige->get($params[2]))){
      echo "Vhost: ".$params[2]." not loaded\n";
      return;
     }
     $this->vhost_storige->remove($params[2]);
     $this->vhost_storige->load($params[2]);
     return;
    }
   }
   echo "Usage vhost list|load 'vhost name'|exit 'vhost name'|reload 'vhost_name'|list all\n";
   return;
  }
  if($command == 'clients'){
   var_dump($this->clients);
   /*foreach($this->clients as $c){
    var_dump($c);
    $c = new \Client\Client($c);
    var_dump($c);
    $c = null;
   }*/
   return;
  }
  if($command == ''){
   return;
  }
  echo "Command '".$command."' not found\n";
 }
 public function stopCommand(){
  return $this->stop;
 }
}