<?php
namespace Server;
class Request{
 private $body;
 private $query;
 private $header;
 private $url;
 private $host;
 private $methotd;
 public function __construct($methotd,\Server\Request\Host $host = null,$url,\Server\Header $header,\Server\Request\Data $query,\Server\Request\Data $body = null){
  $this->body = $body;
  $this->query = $query;
  $this->url = $url;
  $this->host = $host;
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
   $full_url = $this->host->getRawHost().$this->url;
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
 public function getHost(){
  return $this->host;
 }
}
