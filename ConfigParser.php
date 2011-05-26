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
use NoiseLabs\ToolKit\ConfigParser\Exception\NoOptionException;

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
class ConfigParser extends BaseConfigParser implements ConfigParserInterface
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
	 * Get an option value for the named section.
	 * If the option doesn't exist in the configuration $defaults is used.
	 * If $defaults doesn't have this option too then we look for the
	 * $fallback parameter.
	 * If everything fails throw a NoOptionException.
	 *
	 * @param $section 	Section name
	 * @param $option 	Option name
	 * @param $fallback A fallback value to use if the option isn't found in
	 * 					the configuration and $defaults.
	 *
	 * @return Option value (if available)
	 * @throws NoOptionException Couldn't find the desired option in the
	 * configuration, $defaults or as a fallback value.
	 */
	public function get($section, $option, $fallback = null)
	{
		if ($this->hasOption($section, $option)) {
			return $this->_sections[$section][$option];
		}
		// try $defaults
		elseif (isset($this->_defaults[$option])) {
			return $this->_defaults[$option];
		}
		// try $fallback
		elseif (isset($fallback)) {
			return $fallback;
		}
		else {
			throw new NoOptionException($section, $option);
		}
	}

	/**
	 * A convenience method which coerces the option in the specified section
	 * to an integer.
	 */
	public function getInt($section, $option, $fallback = null)
	{
		return (int) $this->get($section, $option);
	}

	/**
	 * A convenience method which coerces the option in the specified section
	 * to a floating point number.
	 */
	public function getFloat($section, $option, $fallback = null)
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
	public function getBoolean($section, $option, $fallback = null)
	{
		if (is_string($value = $this->get($section, $option))) {
			$value = strtolower($value);
		}

		if (in_array($value, $this->_boolean_states)) {
			return $this->_boolean_states[$value];
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

	protected function _buildOutputString()
	{
		$output = '';

		// TODO: write default section first

		foreach ($this->sections() as $section) {
			if (!is_array($this->_sections[$section])) {
				continue;
			}
			// write header tag
			$output .= sprintf("[%s]\n", $section);
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
				$output .= $line.$this->settings->get('linebreak');
			}
			$output .= $this->settings->get('linebreak');
		}

		return $output;
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