ConfigParser - A Configuration File Parser for PHP 5.3
=======================================================

What is ConfigParser?
---------------------

ConfigParser is a configuration file parser for PHP 5.3 heavily inspired by Python's [configparser](http://docs.python.org/dev/library/configparser.html) library.

The ConfigParser class provides a way to read, interpret and write configuration files with structure similar to what’s found in Microsoft Windows INI files.

Requirements
============

* PHP 5.3.2 and up.

License
========

ConfigParser is licensed under the BSD-2 License. See the LICENSE file for details.

Installation
============

Cloning/downloading from [GitHub](https://github.com/noiselabs/noiselabs-php-toolkit) is, so far, the only available method to get this library.

You may clone via git:

	$ git clone git://github.com/noiselabs/noiselabs-php-toolkit.git

or download a tarball either in Gzip or Zip format:

	https://github.com/noisebleed/noiselabs-php-toolkit/archives/master

Documentation
==============

Basic instructions on the usage of the library are presented below.

API-level documentation is available under the `doc` folder in `doc/docblox/`.

Parsed INI files
--------------------

The INI files read by ConfigParser consists of sections, lead by a `[section]` header, and followed by `name = value`  entries.

The option values can contain format strings which refer to other values in
the same section, or values in a special `[DEFAULT]` section.

Usage
-----

### Autoloading classes (optional)

ConfigParser makes use of PHP namespaces and as such the usage of a autoloader libray is recommended. [Symfony](https://github.com/symfony/symfony) provides a great class loader available on [GitHub](https://github.com/symfony/ClassLoader).

To have Symfony's ClassLoader autoloading our classes create a `autoload.php` file  and included it at the top of your scripts.

	<?php
	// autoload.php

	require_once '/path/to/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

	use Symfony\Component\ClassLoader\UniversalClassLoader;

	$loader = new UniversalClassLoader();
	$loader->registerNamespaces(array(
		'Symfony' => '/path/to/symfony/src',
		'NoiseLabs' => '/path/to/noiselabs-php-toolkit/src',
	));
	$loader->register();

	?>

### Basic usage

First take the following INI file as an example:

	[DEFAULT]
	ServerAliveInterval = 45
	Compression = yes
	CompressionLevel = 9
	ForwardX11 = yes

	[github.com]
	user = noiselabs

	[topsecret.server.com]
	Port = 50022
	ForwardX11 = no

Using ConfigParser is as simples as:

	<?php

	namespace Your\Namespace;

	use NoiseLabs\ToolKit\ConfigParser\ConfigParser;

	$cfg = new ConfigParser();

	// parse file
	$cfg->read('/home/user/.config/server.cfg.sample');

	// modify a value (section, option, value)
	$cfg->set('github.com', 'user', 'john');

	// and save it (leave empty to use the last file parsed)
	$cfg->write('/home/user/.config/server.cfg');

	?>

### Using ConfigParser like an associative array

Because it implements `ArrayAccess` the ConfigParser object can be used in a straightforward way:

	<?php

	namespace Your\Namespace;

	use NoiseLabs\ToolKit\ConfigParser\ConfigParser;

	$cfg = new ConfigParser();

	$cfg->read('/home/user/.config/server.cfg');

	// get values
	echo $cfg['github.com']['user'];

	// set options for the 'github.com' section
	$cfg['github.com'] = array('user', 'john');

	?>

### Supported Datatypes

ConfigParser do not guess datatypes of values in configuration files, always storing them internally as strings. This allows reading entries like `pager = false` and keeping values as it is (without any kind of boolean parsing).

This means that if you need other datatypes, you should convert on your own, or use one of these methods:

* Integers:

		$cfg->getInt('topsecret.server.com', 'Port');

* Floats:

		$cfg->getFloat('topsecret.server.com', 'CompressionLevel');

* Booleans:

		$cfg->getBoolean('topsecret.server.com', 'ForwardX11');

### Fallback Values

When using `get()` to pull a value from the configuration you may provide a fallback value in case that option doesn't exist.

	// API: ConfigParser::get($section, $option, $fallback)

Please note that default values have precedence over fallback values. For instance, in our example the 'CompressionLevel' key was specified only in the 'DEFAULT' section. If we try to get it from the section 'topsecret.server.com', we will always get the default, even if we specify a fallback:

	echo $cfg->get('topsecret.server.com', 'CompressionLevel', '3');
	// prints 9

Development
===========

Authors
-------

* Vítor Brandão [ <noisebleed@noiselabs.org> / [@noiselabs](http://twitter.com/noiselabs) / [blog](http://blog.noiselabs.org) ]

Submitting bugs and feature requests
------------------------------------

Bugs and feature requests are tracked on [GitHub](https://github.com/noiselabs/noiselabs-php-toolkit/issues)

Acknowledgements
-----------------

Python's [configparser](http://docs.python.org/dev/library/configparser.html) library was used as a source of inspiration for this library, including documentation and docblocks.