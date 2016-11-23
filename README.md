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