<?php
namespace Plugin;
class Dependence{
 private $name;
 private $version;
 final public function __construct($name,$version = null){
  $this->name = $name;
  if(!is_null($version)){
   if(\Util\Convert::checkVersionFormat($version) == false){
    throw new \Exception("Require ".$name." version: ".$version." have wrong format example '0.0.0'");
   }
  }
  $this->version = $version;
 }
 public function getName(){
  return $this->name;
 }
 public function getVersion(){
  return $this->version;
 }
}