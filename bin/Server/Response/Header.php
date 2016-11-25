<?php
namespace Server\Response;
class Header extends \Server\Header{
 public function __construct(){
  $this->raw_data = null;
  $this->indexed_data = array();
 }
 /**
  * @param bool $build
  * @return string
  */
 public function getRawData($build = false){
  if(is_null($this->raw_data) || $build){
   $this->buildRawData();
  }
  return $this->raw_data;
 }
 /**
  * @param string $key
  * @param string $value
  */
 public function setData($key,$value){
  $this->indexed_data[$key] = $value;
 }
 private function buildRawData(){
  $this->raw_data = "";
  foreach($this->indexed_data as $index=>$value){
   $this->raw_data .= $index.": ".$value."\r\n";
  }
  $this->raw_data .= "\r\n";
 }
}