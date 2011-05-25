ConfigParser - A Configuration File Parser for PHP 5.3
=======================================================

What is ConfigParser?
---------------------

ConfigParser is a configuration file parser for PHP 5.3 heavily inspired by Python's [configparser](http://docs.python.org/dev/library/configparser.html) library.

The ConfigParser class provides a way to read, interpret and write configuration files with structure similar to what’s found in Microsoft Windows INI files.

Requirements
============

* PHP 5.3.2 and up

License
========

ConfigParser is licensed under the BSD-2 License. See the LICENSE file for details.

Installation
============

Cloning/downloading from [GitHub] is the only available method to get this library so far.

You may clone via git:

	$ git clone git://github.com/noisebleed/noiselabs-php-toolkit.gitconfig

or download a tarball either in Gzip o Zip format:

	URL: https://github.com/noisebleed/noiselabs-php-toolkit/archives/master

Documentation
==============

Basic instructions on the usage of the library are presented below.

API-level documentation is available under the `doc` folder in `doc/docblox/`.

About the INI files
--------------------

The INI files read by ConfigParser consists of sections, lead by a "[section]" header, and followed by "name = value" or "name: value" entries, with continuations and such in the style of RFC 822.

The option values can contain format strings which refer to other values in
the same section, or values in a special [DEFAULT] section.

Usage
-----

### Autoloading classes (optional)

ConfigParser makes use of PHP namespaces and as such the usage of a autoloader libray is recommended. Symfony provides a great class loader available on [GitHub](https://github.com/symfony/ClassLoader).

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

Now using ConfigParser is as simples as:

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

Development
===========

Authors
-------

* Vítor Brandão - [email](noisebleed@noiselabs.org) / [twitter](http://twitter.com/noiselabs) / [blog](http://blog.noiselabs.org)

Submitting bugs and feature requests
------------------------------------

Bugs and feature requests are tracked on [GitHub](https://github.com/noiselabs/noiselabs-php-toolkit/issues)
