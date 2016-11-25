<?php
namespace Console;
class Command{
 private $name;
 private $callback;
 private $description;
 /**
  * @param string $name
  * @param null|object $callback_object
  * @param string $callback_methotd
  * @param string $description
  */
 public function __construct($name,$callback_object = null,$callback_methotd = "onConsoleCommand",$description = ""){
  $this->name = $name;
  $this->callback = array($callback_object,$callback_methotd);
  $this->description = $description;
 }
 /**
  * @return string
  */
 public function getName(){
  return $this->name;
 }
 /**
  * @return callable
  */
 public function getCallback(){
  return $this->callback;
 }
 /**
  * @return object
  */
 public function getCallbackObject(){
  return $this->callback[0];
 }
 /**
  * @param object $callback_object
  */
 public function setCallbackObject($callback_object){
  $this->callback[0] = $callback_object;
 }
 /**
  * @return string
  */
 public function getDescription(){
  return $this->description;
 }
}