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

Using ConfigParser is as simples as:

	<?php

	namespace Your\Namespace;

	use NoiseLabs\ToolKit\ConfigParser\ConfigParser;

	$cfg = new ConfigParser();

	// parse file
	$cfg->read('/home/user/.gitconfig');

	// modify a value (section, option, value)
	$cfg->set('color', 'pager', 'true');

	// and save it (leave empty to use the last file parsed)
	$cfg->write('/home/user/.gitconfig-changed');

	?>

### Using ConfigParser like an associative array

Because it implements `ArrayAccess` the ConfigParser object can be used in a straightforward way:

	<?php

	namespace Your\Namespace;

	use NoiseLabs\ToolKit\ConfigParser\ConfigParser;

	$cfg = new ConfigParser();

	$cfg->read('/home/user/.gitconfig');

	// get values
	echo $cfg['color']['pager'];

	// set options for the 'color' section
	$cfg['color'] = array('pager', 'false');

	?>



Supported Datatypes
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