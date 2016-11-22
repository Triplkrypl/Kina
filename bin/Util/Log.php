<?php
namespace Util;
class Log{
 private $config;
 private $log_file;
 public function __construct(\Server\Config $config,$log_dir){
  $this->config = $config;
  $this->log_file = $log_dir."/log.txt";
 }
 public function log($message,$log_type = "",$plugin = "Kina"){
  $format_message = "[".(new \DateTime())->format("d-m-Y H:i:s")."] [".$plugin."]";
  if($log_type != ""){
   $format_message .= " [".$log_type."]";
  }
  $format_message .= " ".$message."\n";
  if($this->config->get("console")){
   echo $format_message;
  }
  $file = \fopen($this->log_file,"a");
  \fwrite($file,$format_message);
  \fclose($file);
 }
 public function logException($add_message,\Exception $e,$log_type = "",$plugin = "Kina"){
  $message = $add_message." '".\get_class($e)."' message: ".$e->getMessage()." from file: ".$e->getFile()." line: ".$e->getLine();
  $this->log($message,$log_type,$plugin);
 }
}