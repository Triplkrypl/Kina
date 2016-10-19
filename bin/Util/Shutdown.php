<?php
namespace Util;
class Shutdown{
 static $server;
 static $init;
 public static function init(\Server $server){
  if(static::$init == false){
   static::$server = $server;
   static::$init = true;
  }
 }
 public static function shutdown($sig_number){
  static::$server->stop();
  exit();
 }
}