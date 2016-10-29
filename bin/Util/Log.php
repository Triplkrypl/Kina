<?php
namespace Util;
class Log{
 public function log($message,$plugin = "Kina"){
  echo "[".(new \DateTime())->format("d-m-Y H:i:s")."] [".$plugin."] ".$message."\n";
 }
}