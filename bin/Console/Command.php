<?php
namespace Console;
class Command{
 private $name;
 private $callback;
 private $description;
 public function __construct($name,$callback,$description = ""){
  $this->name = $name;
  $this->callback = $callback;
  $this->description = $description;
 }
 public function getName(){
  return $this->name;
 }
 public function getCallback(){
  return $this->callback;
 }
 public function getDescription(){
  return $this->description;
 }
}