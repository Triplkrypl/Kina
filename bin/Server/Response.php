<?php
namespace Server;

class Response{ 
 private $status_code;
 private $data;
 private $header;
 public function __construct($data = "",$status_code = 200,\Server\Response\Header $header = null){
  $this->status_code = $status_code;
  $this->header = $header;
  $this->data = $data;   
 }
 public function getStatusCode(){
  return $this->status_code;
 }
 public function getData(){
  return $this->data;
 }
 public function getHeader(){
  return $this->header;
 }
 public function getDataLenght(){
  return \strlen($this->data);
 }
 public function setStatusCode($status_code){
  $this->status_code = $status_code;
 }
 public function setHeader(\Server\Header $header){
  $this->header = $header;
 }
 public function setData($data){
  $this->data = $data;
 }
}