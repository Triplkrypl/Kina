<?php
namespace Server;
class Header{
 protected $raw_data;
 protected $indexed_data;
 public function __construct($raw_data = null){
  $this->raw_data = $raw_data;
  $this->indexed_data = null;
 }
 public function getRawData(){
  return $this->raw_data;
 }
 public function getData($index){
  if(is_null($this->indexed_data)){
   $this->parse();
  }
  if(array_key_exists($index,$this->indexed_data)){
   return $this->indexed_data[$index];
  }
  return null;
 }
 public function getAllData(){
  if(is_null($this->indexed_data)){
   $this->parse();
  }
  return $this->indexed_data;
 }
 private function parse(){
  $this->indexed_data = array();
  if(is_null($this->raw_data)){
   return;
  }
  $rows = \explode("\r\n",$this->raw_data);
  foreach($rows as $row){
   $columns = \explode(": ",$row);
   if(count($columns) == 2){
    $this->indexed_data[$columns[0]] = $columns[1];
   }
  }
 }
}