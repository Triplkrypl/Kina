<?php
namespace Util;
class Convert{
 static function hostToVhostName($host){
  if(is_null($host)){
   return "";
  }
  $pos = \strpos($host,":");
  if($pos !== false){
   $host = \substr($host,0,$pos);
  }
  $vhost = \preg_replace("/([^a-zA-B0-9]+)/","_",$host);
  return $vhost;
 }
 static public function statusCodeToText($code = 200){
  $codes = array(
   200 => "OK",
   404 => "Not found",
   403 => "Forbidden",
  );
  if(array_key_exists($code,$codes)){
   return $codes[$code];
  }
  return "shit happens";
 }
 //@todo delete ???
 static public function fileNameToClassName($base_dir,$file_name){
  $file_name = \str_replace($base_dir."/","",$file_name);
  $class_name = \str_replace("/","\\",$file_name);
  return \str_replace(".php","\\",$class_name);
 }
 static public function firstCharToUp($string){
  return strtoupper($string[0]).substr($string,1,strlen($string));
 }
}