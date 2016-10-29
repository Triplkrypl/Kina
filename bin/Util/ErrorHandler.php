<?php
namespace Util;
class ErrorHandler{
 public function convertError($severity, $message, $file, $line){
  if($severity == 0){
   return;
  }
  throw new \ErrorException($message, 0, $severity, $file, $line);
 }
 public function register(){
  \set_error_handler(array($this,"convertError"));
 }
}