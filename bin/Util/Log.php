<?php
namespace Util;
class Log{
 public function log($message,$vhost = "Kina"){
  echo "[".(new \DateTime())->format("d-m-Y H:i:s")."] [".$vhost."] ".$message."\n";
 }
}