<?php
namespace Server;
class Request{
 private $body;
 private $query;
 private $header;
 private $url;
 private $host;
 private $method;
 public function __construct($method,Request\Host $host = null,$url,Header $header,Request\Data $query,Request\Data $body = null){
  $this->body = $body;
  $this->query = $query;
  $this->url = $url;
  $this->host = $host;
  $this->method = $method;
  $this->header = $header;
 }
 /**
  * @return string
  */
 public function getMethod(){
  return $this->method;
 }
 /**
  * @return \Server\Request\Data
  */
 public function getBody(){
  return $this->body;
 }
 /**
  * @return \Server\Request\Data
  */
 public function getQuery(){
  return $this->query;
 }
 /**
  * @param bool $full
  * @return string
  */
 public function getUrl($full = false){
  if($full){
   $full_url = $this->host->getRawHost().$this->url;
   if($this->query->getRawData() != ""){
    $full_url .= "?".$this->query->getRawData();
   }
   return $full_url;
  }
  return $this->url;
 }
 /**
  * @return \Server\Header
  */
 public function getHeader(){
  return $this->header;
 }
 /**
  * @return \Server\Request\Host|null
  */
 public function getHost(){
  return $this->host;
 }
}
