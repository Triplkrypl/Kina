<?php
namespace Plugin;
class Dependence{
 private $name;
 private $version;
 /**
  * @param string $name
  * @param string|null $version
  * @throws \Exception
  */
 final public function __construct($name,$version = null){
  $this->name = $name;
  if(!is_null($version)){
   if(\Util\Convert::checkVersionFormat($version) == false){
    throw new \Exception("Require ".$name." version: ".$version." have wrong format example '0.0.0'");
   }
  }
  $this->version = $version;
 }
 /**
  * return string
  */
 public function getName(){
  return $this->name;
 }
 /**
  * return string|null
  */
 public function getVersion(){
  return $this->version;
 }
}