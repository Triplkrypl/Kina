<?php
use \Base\Test;
use \Server\Response;
class Base extends \Vhost\Vhost{
 private $a;
 public function onLoad(){
 }
 public function onExit(){
 }
 public function onPhpRequest(\Client\Client $client,\Server\Request $request){
  $test = new Test();
  $response_text = "Test: ".$test->test();
  return new Response(200,null,$response_text);
 }
 /*public function onPhpRequest(\Client\Client $client,\Server\Request $request){
  //$test = new Test();
  //$response_text = "Test: ".$test->test();
  return $this->generateResponse($client,$request);
 }*/
 public function onClientConnect(\Client\Client $client){
  $datum = date("Y-m-d H:i:s");
  echo "Prichozi klient ".$datum.": ".$client->getIp().":".$client->getPort()."\n";
 }
 public function onClientDisconnect(\Client\Client $client){
  $datum = date("Y-m-d H:i:s");
  echo "Odchozi klient ".$datum.": ".$client->getIp().":".$client->getPort()."\n";
 }
 /*public function onNoPhpRequest(\Server\Request $request){
  return null;
 }*/
 private function generateResponse(\Client\Client $client,\Server\Request $request){
  echo "Vhost: ".$this->getName()."\n";
  echo "Url: ".$request->getUrl(true)."\n";
  echo "Client: ".$client->getIp().":".$client->getPort()."\n";
  $response_text = "Client: ".$client->getIp().":".$client->getPort()."\n";
  $response_text .= "Nacteni z vhostu: ".$this->getName()."\n";
  $response_text .= "Url: ".$request->getUrl(true)."\n";
  $response_text .= "Metoda: ".$request->getMethotd()."\n";
  $response_text .= "Hlavicka: \n".$request->getHeader()->getRawData()."\n";
  $response_text .= "Domena: ".$request->getDomain()."\n";
  $header = new \Server\Response\Header();
  //$header->setData("Jsem-na-portu",$this->getServerConfig()->get("port"));
  if(!is_null($request->getBody())){ $response_text .= "Body: ".$request->getBody()->getRawData(); }
  $response = new \Server\Response(200,$header,$response_text);
  return $response;
 }
}