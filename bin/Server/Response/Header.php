<?php
namespace Server\Response;
class Header extends \Server\Header{
 public function __construct(){
  $this->raw_data = null;
  $this->indexed_data = array();
 }
 public function getRawData($build = false){
  if(is_null($this->raw_data) || $build){
   $this->buildRawData();
  }
  return $this->raw_data;
 }
 public function setData($index,$value){
  $this->indexed_data[$index] = $value;
 }
 private function buildRawData(){
  $this->raw_data = "";
  foreach($this->indexed_data as $index=>$value){
   $this->raw_data .= $index.": ".$value."\r\n";
  }
  $this->raw_data .= "\r\n";
 }
}