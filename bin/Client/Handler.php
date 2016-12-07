<?php
namespace Client;
class Handler extends \Thread{
 private $vhost_storige;
 private $error_handler;
 private $client;
 private $clients;
 private $socket;
 private $config;
 private $log;
 public function __construct($socket,\Util\PluginStorige $vhost_storige,\Util\ErrorHandler $error_handler,\Threaded $clients,\Server\Config $config,\Util\Log $log){
  $this->client = new Client($socket);
  $this->socket = $socket;
  $this->vhost_storige = $vhost_storige;
  $this->error_handler = $error_handler;
  $this->clients = $clients;
  $this->config = $config;
  $this->log = $log;
 }
 public function __destruct(){
 }
 public function run(){
  $this->vhost_storige->getClassLoader()->register();
  $this->error_handler->register();
  try{
   try{
    date_default_timezone_set($this->config->get("default_timezone"));
   }
   catch(\Exception $e){
   }
   $this->clientConnect();
   while(1){
    if($this->handle() == false){
     break;
    }
   }
   $this->clientDisconnect();
   unset($this->clients[$this->client->getIp().":".$this->client->getPort()]);
  }
  catch(\Exception $e){
   $this->log->logException("Uncaught exception in client thread",$e,"error");
  }
 }
 private function clientConnect(){
  $this->clients[$this->client->getIp().":".$this->client->getPort()] = $this->socket;
  foreach($this->vhost_storige->getAll() as $vhost){
   try{
    $vhost->onClientConnect($this->client);
   }
   catch(\Exception $e){
    $this->log->logException("Plugin ".$vhost->getName()." throw exception from onClientConnect",$e,"warning");
   }
   $vhost = null;
  }
 }
 private function clientDisconnect(){
  foreach($this->vhost_storige->getAll() as $vhost){
   try{
    $vhost->onClientDisconnect($this->client);
   }
   catch(\Exception $e){
    $this->log->logException("Plugin ".$vhost->getName()." throw exception from onClientDisconnect",$e,"warning");
   }
   $vhost = null;
  }
  \stream_socket_shutdown($this->socket,STREAM_SHUT_RDWR);
 }
 private function selectVhost(\Server\Request $request){
  switch($this->config->get("vhost_select")){
   case "self":{
    foreach($this->vhost_storige->getAll() as $vhost){
     try{
      $result = $vhost->onVhostChoise($request);
     }
     catch(\Exception $e){
      $this->log->logException("Plugin ".$vhost->getName()." throw exception from onVhostChoise",$e,"warning");
      continue;
	 }
     if($result){
      return $vhost;
     }
    }
    break;
   }
   case "auto":{
    if(!is_null($request->getHost())){
	 $vhost = $this->vhost_storige->get(\Util\Convert::hostToVhostName($request->getHost()->getHost()));
	 if(!is_null($vhost)){
	  return $vhost;
	 }
	}
    break;
   }
   case "config":{
    $rules = $this->config->get("vhost_map");
    foreach($rules as $regex=>$vhost_name){
	 if(preg_match($regex,$request->getUrl(true))){
	  $vhost = $this->vhost_storige->get($vhost_name);
	  if(!is_null($vhost)){
	   return $vhost;
	  }
	  break;
	 }
	}
    break;
   }
  }
  return null;
 }
 private function handleRequest(\Server\Request $request){
  $vhost = $this->selectVhost($request);
  if(is_null($vhost)){
   $vhost = $this->vhost_storige->getBase();
  }
  try{
   $result = $vhost->onPhpRequestChoice($request);
  }
  catch(\Exception $e){
   $this->log->logException("Plugin ".$vhost->getName()." throw exception from onPhpRequestChoice",$e,"warning");
   return $vhost->getResponseError(500);
  }
  $methotd = "onNoPhpRequest";
  if($result){
   $methotd = "onPhpRequest";
  }
  try{
   $response = $vhost->$methotd($this->client,$request);
  }
  catch(\Exception $e){
   $this->log->logException("Plugin ".$vhost->getName()." throw exception from ".$methotd,$e,"warning");
   return $vhost->getResponseError(500);
  }
  if(is_null($response)){
   return $vhost->getResponseError(404);
  }
  return $response;
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
  $host = $header->getData("Host");
  if(!is_null($host)){
   $host = new \Server\Request\Host($host);
  }
  $request = new \Server\Request($request_info[0],$host,$url[0],$header,new \Server\Request\Data($query),$body);
  $response = $this->handleRequest($request);
  $data = $http_version." ".$response->getStatusCode()." ".\Util\Convert::statusCodeToText($response->getStatusCode())."\r\n";
  $header = $response->getHeader();
  if(is_null($header)){
   $header = new \Server\Response\Header();
  }
  $header->setData("Content-Length",$response->getDataLength());
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