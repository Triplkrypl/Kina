Kina
====

Kina is simple multithreaded web server with plugin support, which always keep
alive tcp connection and wait until client say goodbye.

Install
-------

Development version

```console
git clone git@github.com:Triplkrypl/Kina.git
```

Dependencies
------------

Php interpreter runable as "php" command 

Php pthreads extension

Install

```console
pecl install pthreads
```

Install on php 5.6 and older

```console
pecl install pthreads-2.0.10
```

Run
---

```console
./run
```

Configuration example
---------------------

Server configuration file "config.json" is generated in same folder (for some fanatic "directory") as run file if
not exists.

```json
{
	"#": "Key start with '#' is comment",
    "#ports": "List of listen TCP ports, default value [80]",
    "ports": [80],
    "#max_connection": "Maximum TCP connection per listen port, default value 100000",
    "max_connection": 100000,
    "#keep_alive_connection_time_out": "Time for client how long he will keep TCP connection open (in minutes), default value 20",
    "keep_alive_connection_time_out": 20,
    "#directory_file_list": "If is request for directory default method 'onNoPhpRequest' will return on true value list of file and directory, default value false",
    "directory_file_list": true,
    "#directory_index": "If is request for directory and directory_file_list is false default method 'onNoPhpRequest' try load content of file directory_index, default value index.html",
    "directory_index": "index.html",
    "#vhost_select0": "Setup selection method for vhost, values config self auto, default value config",
    "#vhost_select1": "config will select vhost by vhost_map kay",
    "#vhost_select2": "auto will transform host name on vhost name",
    "#vhost_select3": "self will call vhost method 'onVhostChoise' if vhost handle request",
    "vhost_select": "config",
    "#vhost_map" => "Object with regex rules for vhots selection, property name is regex and value is vhost name, default value {}",
    "vhost_map": {},
    "#console": "Setup loging messages to console and enable or disable console input, default value true",
    "#console!": "Setup this option to false if you use './run &' Kina will not try read from broken stream",
    "console": true,
    "#default_timezone": "You can set value for function: date_default_timezone_set if is not set in php.ini, default value not exists",
    "default_timezone": "Europe/Prague"
}
```

Creating plugin
---------------

All plugins code is loaded from plugin dir and class is loaded by "PSR-0" standart,
You can not it change and if you do not use this standart, you dig your grave,
kill yourself and bury yourself in afterlife. You can use "require" or "include"
but it is much worse!

Example basic plugin

```php
namespace Test; //Namespace define name of plugin
class Plugin extends \Plugin{ //Main class have to extends "Plugin" class and have to have name "Plugin"

	public function onLoad(){
	}

	public function onExit(){
	}
}
```

Example Base vhost

```php
namespace Base; //Vhost with name "Base" have to be defined!
class Vhost extends \Vhost{ //Main vhost class have same rule as main plugin class, but vhost have more callback and can listen on network

	public function onLoad(){
	}

	public function onExit(){
	}

	/**
	 * @param \Client\Client $client
	 * @param \Server\Request $request
	 * @return \Server\Response|null
	 */
	public function onPhpRequest(\Client\Client $client,\Server\Request $request){
		return null;
	}
}
```