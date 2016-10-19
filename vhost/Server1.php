<?php
class Server1 extends \Vhost\Vhost{
 public function onLoad(){
 }
 public function onExit(){
 }
 public function onPhpRequest(\Client\Client $client,\Server\Request $request){
  $response = new \Server\Response(2000,null,"Funguje");
  return $response;
 }
 public function onVhostChoise(\Server\Request $request){
  if(\preg_match("/^server1.svet-pro-truckery.eu/", $request->getDomain())){
   return true;
  }
  return false;
 }
 public function onNoPhpRequest(\Client\Client $client,\Server\Request $request){
  return $this->getResponseError(404);
 }
}