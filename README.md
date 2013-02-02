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

ConfigParser is licensed under the LGPLv3 License. See the LICENSE file for details.

Installation (Composer)
=======================

### 0. Install Composer

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

``` bash
curl -s http://getcomposer.org/installer | php
```

### 1. Add the noiselabs/configparser package in your composer.json

```js
{
    "require": {
        "noiselabs/configparser": "dev-master"
    }
}
```
Now tell composer to download the package by running the command:

```bash
$ php composer.phar update noiselabs/configparser
```

Composer will install the bundle to your project's `vendor/noiselabs` directory.

Documentation
==============

Basic instructions on the usage of the library are presented below.

Supported INI File Structure
----------------------------

A configuration file consists of sections, each led by a `[section]` header,  followed by `name = value`  entries..

Leading and trailing whitespace is removed from keys and values. Values can be omitted, these will be stored as an empty string.

Configuration files may include comments, prefixed by `;`. Hash marks (`#` ) may no longer be used as comments and will throw a deprecation warning if used.

Usage
-----

### Autoloading classes (optional)

*You may skip this section if you are using Composer.*

ConfigParser makes use of PHP namespaces and as such the usage of a autoloader libray is recommended. [Symfony](https://github.com/symfony/symfony) provides a great class loader available on [GitHub](https://github.com/symfony/ClassLoader).

To have Symfony's ClassLoader autoloading our classes create a `autoload.php` file  and included it at the top of your scripts.

    <?php
    // autoload.php

    require_once '/path/to/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

    use Symfony\Component\ClassLoader\UniversalClassLoader;

    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(array(
        'NoiseLabs' => '/path/to/noiselabs-php-toolkit/src',
    ));
    $loader->register();

    ?>

### Basic usage

First, take the following INI file as an example:

    [DEFAULT]
    ServerAliveInterval = 45
    Compression = yes
    CompressionLevel = 9
    ForwardX11 = yes

    [github.com]
    user = foo

    [topsecret.server.com]
    Port = 50022
    ForwardX11 = no

Using ConfigParser is as simples as:

    <?php

    namespace Your\Namespace;

    use NoiseLabs\ToolKit\ConfigParser\ConfigParser;

    $cfg = new ConfigParser();

    // load file
    $cfg->read('/home/user/.config/server.cfg.sample');

    // modify a value (section, option, value)
    $cfg->set('github.com', 'user', 'bar');

    // and save it
    $cfg->save();

    // ... or, write to another file
    $cfg->write('/home/user/.config/server.cfg');

    ?>

### Using ConfigParser like an associative array

Because it implements `ArrayAccess` the ConfigParser object can be used in a straightforward way:

    $cfg = new ConfigParser();

    $cfg->read('/home/user/.config/server.cfg');

    // get values
    echo $cfg['github.com']['user'];

    // set options for the 'github.com' section
    $cfg['github.com'] = array('user', 'bar');

    ?>

### Iterate

And because ConfigParser implements `IteratorAggregate` it is also possible to use `foreach` to loop over the configuration.

    $cfg = new ConfigParser();

    foreach ($cfg as $section => $name) {
        echo sprintf("Section '%s' has the following options: %s\n",
                    $section,
                    implode(", ", $cfg->options($section))
                    );
    }

### Loading multiple files at once

This is designed so that you can specify a list of potential configuration file locations (for example, the current directory, the user’s home directory, and some system-wide directory), and all existing configuration files in the array will be read.

    $cfg = new ConfigParser();

    $cfg->read(array('/etc/myapp.cfg', '/usr/local/etc/myapp.cfg', '/home/user/.config/myapp.cfg');

### Parsing files without sections

ConfigParser was designed to work with INI files with section tags. For simple files with just `option = value` entries `NoSectionsConfigParser` can be used.

    <?php

    namespace Your\Namespace;

    use NoiseLabs\ToolKit\ConfigParser\NoSectionsConfigParser;

    $cfg = NoSectionsConfigParser();

    $cfg->read('/tmp/sectionless.cfg');

    $cfg->set('server', '192.168.1.1.');

    echo $cfg->get('server');

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

Customizing Parser Behaviour
----------------------------

### Loading a set of default options/values

You may create an array of key-value pairs and pass them to the constructor as the first argument. These option/values will be initially put in the DEFAULT section. This makes for an elegant way to support concise configuration files that don’t specify values which are the same as the documented default.

    // define some defaults values
    $defaults = array(
                    'Compression' 		=> 'yes',
                    'CompressionLevel' 	=> 9
                    );

    $cfg = new ConfigParser($defaults);

### Advanced configuration

ConfigParser includes a small set of internal options to change the way it writes to configuration files or if exceptions are to be raised.

* **delimiter** - The delimiter character to use between keys and values (when writing). Defaults to `= `.
*  **space_around_delimiters** - Inserts (or not) a blank space between keys/values and delimiters. Defaults to `TRUE`.
*  **linebreak** - The linebreak to use. Defaults to `'\r\n'` on Windows OS and `'\n'` on every other OS (Linux, Mac).
* **throw_exceptions** - Use this option to disable exceptions. If set to false ConfigParser will write to the error log instead. Defaults to `TRUE`.

### Using a custom error logger

If you have disabled PHP exceptions (see above section) ConfigParser will use `error_log()` to record the exception message. In this scenario you may want to use a custom error logger instead of `error_log`, or even disable logging at all.

To override the original logger method just extend ConfigParser and replace `ConfigParser::log()` with your own implementation.

[Monolog](https://github.com/Seldaek/monolog) is a great logging library for PHP 5.3 and will be used as our custom logger in the following example.

    <?php

    namespace Your\Namespace;

    use NoiseLabs\ToolKit\ConfigParser;
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;

    class MyConfigParser extends ConfigParser
    {
        protected $logger;

        public function __construct(array $defaults = array(), array $settings = array())
        {
            parent::__construct($defaults, $settings);

            // create a log channel
            $this->logger = new Logger('ConfigParser');
            $this->logger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));
        }

        public function log($message)
        {
            // add records to the log
            $this->logger->addError($message);
        }
    }

    ?>

Development
===========

Authors
-------

* Vítor Brandão - <noisebleed@noiselabs.org> / [twitter](http://twitter.com/noiselabs) / [blog](http://blog.noiselabs.org)

Submitting bugs and feature requests
------------------------------------

Bugs and feature requests are tracked on [GitHub](https://github.com/noiselabs/noiselabs-php-toolkit/issues).

Acknowledgements
-----------------

Python's [configparser](http://docs.python.org/dev/library/configparser.html) library was used as a source of inspiration for this library, including documentation and docblocks.
