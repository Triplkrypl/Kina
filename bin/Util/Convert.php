<?php
namespace Util;
class Convert{
 static function hostToVhostName($host){
  $len = strlen($host);
  $vhost = "";
  for($i=0; $i<$len; $i++){
   $ch = $host[$i];
   if($i == 0){
    $vhost .= strtoupper($ch);
    continue;
   }
   if($host[$i-1] == "."){
    $vhost .= strtoupper($ch);
    continue;
   }
   if($host[$i] == "."){
    continue;
   }
   $vhost .= $ch;
  }
  return $vhost;
 }
 static public function statusCodeToText($code = 200){
  $codes = array(
   200 => "OK",
   404 => "Not found",
   403 => "Forbidden",
   500 => "Internal server error",
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