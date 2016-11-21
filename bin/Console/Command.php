<?php
namespace Console;
class Command{
 private $name;
 private $callback;
 private $description;
 public function __construct($name,$callback_object = null,$callback_methotd = "onConsoleCommand",$description = ""){
  $this->name = $name;
  $this->callback = array($callback_object,$callback_methotd);
  $this->description = $description;
 }
 public function getName(){
  return $this->name;
 }
 public function getCallback(){
  return $this->callback;
 }
 public function getCallbackObject(){
  return $this->callback[0];
 }
 public function setCallbackObject($callback_object){
  $this->callback[0] = $callback_object;
 }
 public function getDescription(){
  return $this->description;
 }
}