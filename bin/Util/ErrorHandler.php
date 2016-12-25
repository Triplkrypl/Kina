<?php
namespace Util;
class ErrorHandler{
 private $log;
 private $socket;
 private $clients;
 private $client;
 public function convertError($severity, $message, $file, $line){
  if($severity == 0){
   return;
  }
  throw new \ErrorException($message, 0, $severity, $file, $line);
 }
 public function catchShutdownError(){
  $error = \error_get_last();
  if(!is_null($error)){
   $this->log->log("Critical fail in client thread message: ".$error["message"]." file: ".$error["file"]." line: ".$error["line"],"error","Kina",true);
   try{
    \stream_socket_shutdown($this->socket,STREAM_SHUT_RDWR);
   }
   catch(\Exception $e){
   }
   try{
    \fclose($this->socket);
   }
   catch(\Exception $e){
   }
   unset($this->clients[$this->client->getIp().":".$this->client->getPort()]);
  }
 }
 public function register(){
  \set_error_handler(array($this,"convertError"));
 }
 public function registerShutdown(\Util\Log $log,$socket,\Threaded $clients,\Client\Client $client){
  \register_shutdown_function(array($this,"catchShutdownError"));
  $this->log = $log;
  $this->socket = $socket;
  $this->clients = $clients;
  $this->client = $client;
 }
}