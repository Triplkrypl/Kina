<?php
namespace Server;
class Response{ 
 private $status_code;
 private $data;
 private $header;
 /**
  * @param string $data
  * @param int $status_code
  * @param \Server\Response\Header $header
  */
 public function __construct($data = "",$status_code = 200,Response\Header $header = null){
  $this->status_code = $status_code;
  $this->header = $header;
  $this->data = $data;   
 }
 /**
  * @return int
  */
 public function getStatusCode(){
  return $this->status_code;
 }
 /**
  * @return string
  */
 public function getData(){
  return $this->data;
 }
 /**
  * @return \Server\Response\Header
  */
 public function getHeader(){
  return $this->header;
 }
 /**
  * @return int
  */
 public function getDataLength(){
  return \strlen($this->data);
 }
 /**
  * @param int $status_code
  */
 public function setStatusCode($status_code){
  $this->status_code = $status_code;
 }
 /**
  * @param \Server\Header $header
  */
 public function setHeader(Header $header){
  $this->header = $header;
 }
 /**
  * @param string $data
  */
 public function setData($data){
  $this->data = $data;
 }
}