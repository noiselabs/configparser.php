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

use NoiseLabs\ToolKit\ConfigParser\File;
use NoiseLabs\ToolKit\ConfigParser\Exception\NoOptionException;

/**
 * This class is a version of the ConfigParser class meant to be used for
 * configuration files that don't have sections.
 *
 * @author Vítor Brandão <noisebleed@noiselabs.org>
 */
class NoSectionsConfigParser extends BaseConfigParser implements NoSectionsConfigParserInterface
{
	const HAS_SECTIONS		= false;

	/**
	 * Return a list of options available
	 */
	public function options()
	{
		return array_keys($this->_sections);
	}

	/**
	 * If the given option exists, return TRUE; otherwise return FALSE.
	 */
	public function hasOption($option)
	{
		return isset($this->_sections[$option]);
	}

	/**
	 * Get an option value for the named section.
	 * If the option doesn't exist in the configuration $defaults is used.
	 * If $defaults doesn't have this option too then we look for the
	 * $fallback parameter.
	 * If everything fails throw a NoOptionException.
	 *
	 * @param $option 	Option name
	 * @param $fallback A fallback value to use if the option isn't found in
	 * 					the configuration.
	 *
	 * @return Option value (if available)
	 * @throws NoOptionException Couldn't find the desired option in the
	 * configuration or as a fallback value.
	 */
	public function get($option, $fallback = null)
	{
		if ($this->hasOption($option)) {
			return $this->_sections[$option];
		}
		// try $fallback
		elseif (isset($fallback)) {
			return $fallback;
		}
		else {
			if ($this->_throwExceptions()) {
				throw new NoOptionException('<None>', $option);
			}
			else {
				error_log(sprintf("Option '%s' wasn't found", $option));
				return null;
			}
		}
	}

	/**
	 * A convenience method which coerces the option value to an integer.
	 */
	public function getInt($option, $fallback = null)
	{
		return (int) $this->get($option);
	}

	/**
	 * A convenience method which coerces the option value to a floating
	 * point number.
	 */
	public function getFloat($option, $fallback = null)
	{
		return (float) $this->get($option);
	}

	/**
	 * A convenience method which coerces the option value to a Boolean value.
	 * Note that the accepted values for the option are '1', 'yes', 'true',
	 * and 'on', which cause this method to return TRUE, and '0', 'no',
	 * 'false', and 'off', which cause it to return FALSE.
	 * These string values are checked in a case-insensitive manner. Any
	 * other value will cause it to raise ValueException.
	 */
	public function getBoolean($option, $fallback = null)
	{
		if (is_string($value = $this->get($option, $fallback))) {
			$value = strtolower($value);
		}

		if (isset($this->_boolean_states[$value])) {
			return $this->_boolean_states[$value];
		}
		else {
			$error_msg = "Option '".$option."' is not a boolean";
			if ($this->_throwExceptions()) {
				throw new \UnexpectedValueException($error_msg);
			}
			else {
				error_log($error_msg);
				return null;
			}
		}
	}

	public function _buildOutputString()
	{
		$output = '';

		foreach ($this->_sections as $key => $value) {
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

		return $output;
	}
}