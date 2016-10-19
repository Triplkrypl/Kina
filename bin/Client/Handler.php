<?php
namespace Client;
class Handler extends \Thread{
 private $vhost_storige;
 private $client;
 private $clients;
 private $socket;
 private $config;
 public function __construct($socket,\Util\PluginStorige $vhost_storige,\Threaded $clients,\Server\Config $config){
  $this->client = new Client($socket);
  $this->socket = $socket;
  $this->vhost_storige = $vhost_storige;
  $this->clients = $clients;
  $this->config = $config;
 }
 public function __destruct(){
 }
 public function run(){
  \spl_autoload_register("\Util\ClassLoader::loadClass");
  \date_default_timezone_set("Europe/Prague");
  $this->clients[] = $this->socket;
  var_dump("Seznam clientu");
  var_dump($this->clients);
  /*foreach($this->clients as $c){
   $c = new Client($c);
   var_dump($c);
   $c = null;
  }*/
  $this->vhost_storige->checkAllPlugin();
  foreach($this->vhost_storige->getAll() as $vhost){
   $vhost->onClientConnect($this->client);
   $vhost = null;
  }
  while(1){
   if($this->handle() == false){
    break;
   }
  }
  $this->clientDisconnect();
 }
 private function clientDisconnect(){
  $this->vhost_storige->checkAllPlugin();
  foreach($this->vhost_storige->getAll() as $vhost){
   $vhost->onClientDisconnect($this->client);
   $vhost = null;
  }
  foreach($this->clients as $key=>$client){
   if(new Client($client) == $this->client){
    unset($this->clients[$key]);
    $client = null;
    break;
   }
   $client = null;
  }
  \stream_socket_shutdown($this->socket,STREAM_SHUT_RDWR);
 }
 private function selectVhost(\Server\Request $request){
  if($this->config->get("vhost_self_select")){
   foreach($this->vhost_storige->getAll() as $vhost){
    if($vhost->onVhostChoise($request)){
     return $vhost;
    }
   }
  }else{
   //!!!!!!!!!!!!!!!add config rule
  }
  return null;
 }
 private function handle(){
  $request_info = null; $header = null; $temp_message = null;
  $result = $this->getHttpHeader($request_info,$header,$temp_message);
  if($result == false){
   return false;
  }
  $body = null;
  if(!is_null($header->getData("Content-Length")) && $header->getData("Content-Length") > 0){
   $result = $this->getHttpBody($header,$body,$temp_message);
   if($result == false){
    return false;
   }
  }
  $temp_message = null;
  $request_info = explode(" ",$request_info);
  $http_version = $request_info[2];
  $url = $request_info[1];
  $url = \explode("?",$url);
  $query = "";
  if(count($url) == 2){
   $query = $url[1];
  }
  $request = new \Server\Request($request_info[0],$header->getData("Host"),$url[0],$header,new \Server\Request\Data($query),$body);
  $this->vhost_storige->checkAllPlugin();
  $vhost = $this->selectVhost($request);
  if(is_null($vhost)){
   $vhost = $this->vhost_storige->getDefault();
  }
  $response = null;
  if($vhost->onPhpRequestChoice($request)){
   $response = $vhost->onPhpRequest($this->client,$request);
  }else{
   $response = $vhost->onNoPhpRequest($this->client,$request);
  }
  if(is_null($response)){
   $response = $vhost->getResponseError(404);
  }
  $data = $http_version." ".$response->getStatusCode()." ".\Util\Convert::statusCodeToText($response->getStatusCode())."\r\n";
  $header = $response->getHeader();
  if(is_null($header)){
   $header = new \Server\Response\Header();
  }
  $header->setData("Content-Length",$response->getDataLenght());
  $header->setData("Connection","keep-alive");
  $header->setData("Keep-Alive","timeout=".($this->config->get("keep_alive_connection_time_out")*60));
  $data .= $header->getRawData(true);
  $data .= $response->getData();
  \fwrite($this->socket,$data,strlen($data));
  $response = null;
  $request = null;
  $header = null;
  $body = null;
  $data = null;
  return true;
 }
 private function getHttpBody(\Server\Header $header,\Server\Request\Data &$body = null,&$temp_message){
  $content_lenght = $header->getData("Content-Length");
  while(1){
   $data_read = $content_lenght - strlen($temp_message);
   if($data_read > 1000){
    $data_read = 1000;
   }
   if($data_read != 0){
    $data = @\fread($this->socket,$data_read);
    if(strlen($data) == 0){
     return false;
    }
    $temp_message .= $data;
   }
   if($this->checkEndBody($header,$temp_message)){
    break;
   }
  }
  $body = new \Server\Request\Data($temp_message);
  return true;
 }
 private function checkEndBody(\Server\Header $header,&$temp_message){
  return strlen($temp_message) == $header->getData("Content-Length");
 }
 private function getHttpHeader(&$request_info,&$header,&$temp_message){
  $temp_message = "";
  while(1){
   $data = @\fread($this->socket,1000);
   if(strlen($data) == 0){
    return false;
   }
   $temp_message .= $data;
   $end_header = $this->checkEndHeader($temp_message);
   if(!is_null($end_header)){
    break;
   }
  }
  $header_message = substr($temp_message,0,$end_header+1);
  $rest_temp = ($end_header+1)-strlen($temp_message);
  $temp_message = $rest_temp == 0 ? "" : substr($temp_message,$rest_temp);
  $header_lines = \explode("\r\n",$header_message);
  $request_info = $header_lines[0];
  $header = new \Server\Header(\str_replace($header_lines[0]."\r\n","",$header_message));
  return true;
 }
 private function checkEndHeader(&$temp_message){
  $message_lengh = strlen($temp_message);
  $konec = 0;
  $i = 0;
  for($i = 0; $i < $message_lengh; $i++){
   if($temp_message[$i] == "\r"){
    if($konec == 1){
     $konec = 2;
    }else{
     $konec = 1;
    }
    continue;
   }
   if($temp_message[$i] != "\n"){
    $konec = 0;
   }
   if($konec == 2){
    break;
   }
  }
  if($konec == 2){
   return $i;
  }
  return null;
 }
}