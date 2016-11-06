<?php
abstract class Vhost extends \Plugin{
 public function getResponseError($http_code){
  $data = "
<html>
 <body>
  <h1>".$http_code." ".\Util\Convert::statusCodeToText($http_code)."</h1>
 </body>
</html>";
  return new \Server\Response($data,$http_code,null);
 }
 public function onPhpRequestChoice(\Server\Request $request){
  return \preg_match("/.*\.php$/",$request->getUrl());
 }
 public function onClientConnect(\Client\Client $client){
 }
 public function onClientDisconnect(\Client\Client $client){
 }
 public function onVhostChoise(\Server\Request $request){
  if($this->getName() == "Base"){
   return false;
  }
  if(is_null($request->getHost())){
   return false;
  }
  return \Util\Convert::hostToVhostName($host->getHost()) == $this->getName();
 }
 public function onPhpRequest(\Client\Client $client,\Server\Request $request){
  return null;
 }
 public function onNoPhpRequest(\Client\Client $client,\Server\Request $request){
  $static_data_dir = $this->getDataDir()."/static".$request->getUrl();
  if(file_exists($static_data_dir)){
   if(is_file($static_data_dir)){
    return new \Server\Response(200,null,file_get_contents($static_data_dir));
   }
   if(is_dir($static_data_dir)){
    return $this->getDirectoryContent($request,$static_data_dir);
   }
  }
  return null;
 }
 protected function getDirectoryContent(\Server\Request $request,$static_data_dir){
  $index = $this->getIndexInDirectory($static_data_dir);
  if(!is_null($index)){
   return $index;
  }
  if($this->getServerConfig()->get("directory_file_list")){
   return $this->getFileInDirectory($request,$static_data_dir);
  }
  return $this->getResponseError(403);
 }
 protected function getIndexInDirectory($static_data_dir){
  if($this->getServerConfig()->exists("directory_index")){
   $index = $static_data_dir."/".$this->getServerConfig()->get("directory_index");
   if(is_file($index)){
    return new \Server\Response(file_get_contents($index),200,null);
   }
  }
  return null;
 }
 protected function getFileInDirectory(\Server\Request $request,$static_data_dir){
  $files = \scandir($static_data_dir);
  unset($files[0]);
  if($request->getUrl() == "/"){
   unset($files[1]);
  }
  $data = "<html>\n";
  $data .= " <body>\n<h1>".$request->getUrl()."</h1>\n";
  foreach($files as $key=>$file){
   $url = $request->getUrl();
   if($request->getUrl() != "/"){
    $url .= "/";
   }
   $url .= $file;
   $files[$key] = "<a href='".$url."'>".$file."</a>";
  }
  $data .= \implode("\n<br />",$files);
  $data .= " </body>\n";
  $data .= "<html>";
  return new \Server\Response($data,200,null);
 }
}