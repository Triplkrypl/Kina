<?php
namespace Server;
class Request{
 private $body;
 private $query;
 private $header;
 private $url;
 private $domain;
 private $methotd;
 public function __construct($methotd,$domain,$url,\Server\Header $header,\Server\Request\Data $query,\Server\Request\Data $body = null){
  $this->body = $body;
  $this->query = $query;
  $this->url = $url;
  $this->domain = $domain;
  $this->methotd = $methotd;
  $this->header = $header;
 }
 public function getMethotd(){
  return $this->methotd;
 }
 public function getBody(){
  return $this->body;
 }
 public function getQuery(){
  return $this->query;
 }
 public function getUrl($full = false){
  if($full){
   $full_url = $this->domain.$this->url;
   if($this->query->getRawData() != ""){
    $full_url .= "?".$this->query->getRawData();
   }
   return $full_url;
  }
  return $this->url;
 }
 public function getHeader(){
  return $this->header;
 }
 public function getDomain(){
  return $this->domain;
 }
}
