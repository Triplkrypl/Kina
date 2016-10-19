#!/usr/bin/env php
<?php
set_time_limit(0);
ini_set('memory_limit', '128M');
date_default_timezone_set("Europe/Prague");
require_once __DIR__."/bin/Util/ClassLoader.php";
$data_dir = __DIR__."/data";
$loader_object = new \Util\ClassLoader(__DIR__."/vhost");
$loader_object::init(__DIR__."/bin");
\spl_autoload_register("\Util\ClassLoader::loadClass");
declare(ticks = 1);
\pcntl_signal(\SIGTERM,"\Util\Shutdown::shutdown");
\pcntl_signal(\SIGINT,"\Util\Shutdown::shutdown");
$config = new \Server\Config();
$server = new \Server($config,$loader_object,$data_dir);
\Util\Shutdown::init($server);
if($server->loadVhosts()){
 if($server->run()){
  $server->handleClients();
 }
}
$server->stop();
