<?php
namespace Server\Request;
class Data{
 private $raw_data;
 public function __construct($raw_data){
  $this->raw_data = $raw_data;
 }
 /**
  * @return string
  */
 public function getRawData(){
  return $this->raw_data;
 }
 /**
  * @return string[]
  */
 public function getData(){
  $array = \explode("&",$this->raw_data);
  $data = array();
  foreach($array as $index){
   $key = \explode("=",$index);
   if($key[0] != ""){
    $data[$key[0]] = \urldecode($key[1]);
   }
  }
  return $data;
 }
}