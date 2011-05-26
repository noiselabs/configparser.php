<?php
/**
 * @category NoiseLabs
 * @package ConfigParser
 * @version 0.1.0
 * @author Vítor Brandão <noisebleed@noiselabs.org>
 * @copyright (C) 2011 Vítor Brandão <noisebleed@noiselabs.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NoiseLabs\ToolKit\ConfigParser;

use NoiseLabs\ToolKit\ConfigParser\BaseConfigParser;
use NoiseLabs\ToolKit\ConfigParser\File;
use NoiseLabs\ToolKit\ConfigParser\Exception\DuplicateSectionException;
use NoiseLabs\ToolKit\ConfigParser\Exception\NoSectionException;

/**
 * The ConfigParser class implements a basic configuration language which
 * provides a structure similar to what’s found in Microsoft Windows INI
 * files. You can use this to write PHP programs which can be customized by
 * end users easily.
 *
 * DISCLAIMER:
 * Every docblock was shameless copied or at least adapted from Python's
 * configparser documentation page (version 3.0). See
 * http://docs.python.org/dev/library/configparser.html
 *
 * @note This class does not interpret or write the value-type prefixes
 * used in the Windows Registry extended version of INI syntax.
 *
 * @author Vítor Brandão <noisebleed@noiselabs.org>
 */
Class ConfigParser extends BaseConfigParser implements ConfigParserInterface
{
	const DEFAULT_SECTION 	= 'DEFAULT';
	const HAS_SECTIONS		= true;

	/**
	 * Return an associative array containing the instance-wide defaults.
	 */
	public function defaults()
	{
		return $this->_defaults;
	}

	/**
	 * Return a list of the sections available; the default section is not
	 * included in the list.
	 */
	public function sections()
	{
		return array_keys($this->_sections);
	}

	/**
	 * Add a section named section to the instance. If a section by the given
	 * name already exists, DuplicateSectionException is raised. If the
	 * default section name is passed, InvalidArgumentException is raised.
	 * The name of the section must be a string; if not,
	 * InvalidArgumentException is raised too.
	 */
	public function addSection($section)
	{
		// Raise InvalidArgumentException if name is DEFAULT or any of it's
		// case-insensitive variants.
		if (strtolower($section) == 'default') {
			throw new \InvalidArgumentException('Invalid section name: '.$section);
		}

		// Raise InvalidArgumentException if the name of the section is not
		// a string
		if (!is_string($section)) {
			throw new \InvalidArgumentException('Invalid type: expecting a string');
		}

		if (false === $this->hasSection($section)) {
			$this->_sections[(string) $section] = array();
		}
		else {
			throw new DuplicateSectionException($section);
		}
	}

	/**
	 * Indicates whether the named section is present in the configuration.
	 * The default section is not acknowledged.
	 */
	public function hasSection($section)
	{
		return isset($this->_sections[$section]);
	}

	/**
	 * Return a list of options available in the specified section.
	 */
	public function options($section)
	{
		if (true === $this->hasSection($section)) {
			return array_keys($this->_sections[$section]);
		}
		else {
			throw new NoSectionException($section);
		}
	}

	/**
	 * If the given section exists, and contains the given option, return
	 * TRUE; otherwise return FALSE. If the specified section is NULL or an
	 * empty string, DEFAULT is assumed.
	 */
	public function hasOption($section, $option)
	{
		return isset($this->_sections[$section][$option]);
	}

	/**
	 * @throws NoSectionException if section doesn't exist
	 */
	public function setOptions($section, array $options = array())
	{
		if ($this->hasSection($section)) {
			$this->_sections[$section] = $options;
		}
		else {
			throw new NoSectionException($section);
		}
	}

	protected function _read($filename)
	{
		return parse_ini_file($filename, static::HAS_SECTIONS);
	}

	/**
	 * Attempt to read and parse a list of filenames, returning a list of
	 * filenames which were successfully parsed. If filenames is a string, it
	 * is treated as a single filename. If a file named in filenames cannot be
	 * opened, that file will be ignored. This is designed so that you can
	 * specify a list of potential configuration file locations (for example,
	 * the current directory, the user’s home directory, and some system-wide
	 * directory), and all existing configuration files in the list will be
	 * read. If none of the named files exist, the ConfigParser instance will
	 * contain an empty dataset. An application which requires initial values
	 * to be loaded from a file should load the required file or files using
	 * read_file() before calling read() for any optional files:
	 */
	public function read($filenames = array())
	{
		if (!is_array($filenames)) {
			$filenames = array($filenames);
		}

		foreach ($filenames as $filename) {
			if (is_readable($filename)) {
				// register a new file...
				$this->_files[] = new File($filename, 'rb');
				// ... and append configuration
				$this->_sections = array_merge(
								$this->_sections,
								$this->_read($filename)
					);
			}
		}
	}

	public function readFile($filehandler)
	{
		trigger_error(__METHOD__.' is not implemented yet');
	}

	public function readString($string)
	{
		$this->_sections = parse_ini_string($string, static::HAS_SECTIONS);
	}

	public function readArray(array $array = array())
	{
		$this->_sections = $array;
	}

	/**
	 * Re-read configuration from all successfully parsed files.
	 */
	public function reload()
	{
		$filenames = array();
		foreach ($this->_files as $file) {
					$this->_sections = array_merge(
							$this->_sections,
							$this->_read($file->getPathname())
					);
		}
	}

	/**
	 * Get an option value for the named section.
	 */
	public function get($section, $option)
	{
		if ($this->hasOption($section, $option)) {
			return $this->_sections[$section][$option];
		}
		else {
			throw new NoOptionException($section, $option);
		}
	}

	/**
	 * A convenience method which coerces the option in the specified section
	 * to an integer.
	 */
	public function getInt($section, $option)
	{
		return (int) $this->get($section, $option);
	}

	/**
	 * A convenience method which coerces the option in the specified section
	 * to a floating point number.
	 */
	public function getFloat($section, $option)
	{
		return (float) $this->get($section, $option);
	}

	/**
	 * A convenience method which coerces the option in the specified section
	 * to a Boolean value. Note that the accepted values for the option are
	 * '1', 'yes', 'true', and 'on', which cause this method to return TRUE,
	 * and '0', 'no', 'false', and 'off', which cause it to return FALSE.
	 * These string values are checked in a case-insensitive manner. Any
	 * other value will cause it to raise ValueException.
	 */
	public function getBoolean($section, $option)
	{
		$true = array(true, 1, 'true', 'on');
		$false = array(false, 0, 'false', 'off');

		if (is_string($value = $this->get($section, $option))) {
			$value = strtolower($value);
		}

		if (in_array($value, $true)) {
			return true;
		}
		elseif (in_array($value, $false)) {
			return false;
		}
		else {
			throw new \UnexpectedValueException("Option '".$option."' in section '".$section."' is not a boolean");
		}
	}

	/**
	 * If the given section exists, set the given option to the specified
	 * value; otherwise raise NoSectionException.
	 *
	 * @todo Option and value must be strings; if not, TypeException is raised.
	 */
	public function set($section, $option, $value)
	{
		if (true === $this->hasSection($section)) {
			$this->_sections[$section][$option] = (string) $value;
		}
		else {
			throw new NoSectionException($section);
		}

		return $this;
	}

	/**
	 * Write an .ini-format representation of the configuration state
	 *
	 * @throws RuntimeException if file is not writable
	 */
	public function write($filename)
	{
		$file = new File($filename);

		if (!$file->open('cb')) {
			throw new \RuntimeException('File '.$file->getPathname().' could not be opened for writing');
			return false;
		}
		elseif (!$file->isWritable()) {
			throw new \RuntimeException('File '.$file->getPathname().' is not writable');
			return false;
		}

		// TODO: write default section first

		foreach ($this->sections() as $section) {
			// write header tag
			$file->write(sprintf("[%s]\n", $section));
			// and then all options in this section
			foreach ($this->_sections[$section] as $key => $value) {
				// option name
				$line = $key;
				// space before delimiter?
				if ($this->settings->get('space_around_delimiters') &&
				$this->settings->get('delimiter') != ':') {
					$line .= ' ';
				}
				// insert delimiter
				$line .= $this->settings->get('delimiter');
				// space after delimiter?
				if ($this->settings->get('space_around_delimiters')) {
					$line .= ' ';
				}
				// and finally, option value
				$line .= $value;
				// record it for eternity
				$file->write($line.$this->settings->get('linebreak'));
			}
			$file->write($this->settings->get('linebreak'));
		}

		$file->close();
	}

	/**
	 * Remove the specified option from the specified section. If the section
	 * does not exist, raise NoSectionException. If the option existed to be
	 * removed, return TRUE; otherwise return FALSE.
	 */
	public function removeOption($section, $option)
	{
		if (true === $this->hasSection($section)) {
			if (isset($this->_sections[$section][$option])) {
				unset($this->_sections[$section][$option]);
				return true;
			}
			else {
				return false;
			}
		}
		else {
			throw new NoSectionException($section);
		}
	}
}

?>