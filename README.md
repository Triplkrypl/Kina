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

Server configuration file "config.json" is generated in same folder (for some fanatic "direcoty") as run file if
not exists.

```json
{
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
    "#vhost_map": ", default value {}",
    "vhost_map": {},
    "#console": "Setup loging messages to console and enable or disable console input, default value true",
    "console": true,
    "#default_timezone": "You can set value for function: date_default_timezone_set if is not set in php.ini, default value not exists",
    "default_timezone": "Europe/Prague"
}
```