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

use NoiseLabs\ToolKit\ConfigParser\ParameterBag;

abstract class BaseConfigParser implements \ArrayAccess, \IteratorAggregate, \Countable
{
	const VERSION = '0.1.0';

	/**
	 * A set of internal options used when parsing and writing files.
	 *
	 * Known settings:
	 *
	 *  'delimiter':
	 * 		The delimiter character to use between keys and values.
	 *		Defaults to '='.
	 *
	 *  'space_around_delimiters':
	 *		Put a blank space between keys/values and delimiters?
	 *		Defaults to TRUE.
	 *
	 *  'linebreak':
	 *		The linebreak to use.
	 *		Defaults to '\r\n' on Windows OS and '\n' on every other OS.
	 *
	 *  'interpolation':
	 *		@todo: Describe the interpolation mecanism.
	 *		Defaults to FALSE.
	 */
	public $settings = array();

	/**
	 *
	 * @var array
	 */
	protected $_defaults = array();

	/**
	 * The configuration representation is stored here.
	 * @var array
	 */
	protected $_sections = array();

	/**
	 * An array of FILE objects representing the loaded files.
	 * @var array
	 */
	protected $_files = array();

	/**
	 * Booleans alias
	 * @var array
	 */
	protected $_boolean_states = array(
					'1' 	=> true,
					'yes' 	=> True,
					'true'	=> true,
					'on'	=> true,
					'0' 	=> false,
					'no'	=> false,
					'false'	=> false,
					'off'	=> false
					);

	/**
	 * Constructor.
	 *
	 * @param array $defaults
	 * @param array $settings
	 */
	public function __construct(array $defaults = array(), array $settings = array())
	{
		$this->_defaults = $defaults;
		// default options
		$this->settings = new ParameterBag(array(
							'delimiter'					=> '=',
							'space_around_delimiters' 	=> true,
							'linebreak'					=> "\n",
							'throw_exceptions'			=> true,
							'interpolation'				=> false
							));

		if (!isset($settings['linebreak'])) {
			/*
			* OS detection to define the linebreak.
			* For Windows we use "\r\n".
			* For everything else "\n" is used.
			*/
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$this->settings->set('linebreak', "\r\n");
			}
		}

		$this->settings->add($settings);
	}

	/**
	 * Return an associative array containing the instance-wide defaults.
	 */
	public function defaults()
	{
		return $this->_defaults;
	}

	/**
	 * Note the usage of INI_SCANNER_RAW to avoid parser_ini_files from
	 * parsing options and transforming 'false' values to empty strings.
	 */
	protected function _read($filename)
	{
		return parse_ini_file($filename, static::HAS_SECTIONS, INI_SCANNER_RAW);
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
				$this->_sections = array_replace(
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
		$this->_sections = parse_ini_string($string, static::HAS_SECTIONS, INI_SCANNER_RAW);
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

	abstract protected function _buildOutputString();

	/**
	 * Write an .ini-format representation of the configuration state
	 *
	 * @throws RuntimeException if file is not writable
	 */
	public function write($filename)
	{
		$file = new File($filename);

		if (!$file->open('cb')) {
			$errmsg = 'Unable to write configuration as file '.$file->getPathname().' could not be opened for writing';
			if ($this->_throwExceptions()) {
				throw new \RuntimeException($errmsg);
			}
			else {
				error_log($errmsg);
				return false;
			}
		}
		elseif (!$file->isWritable()) {
			$errmsg = 'Unable to write configuration as file '.$file->getPathname().' is not writable';
			if ($this->_throwExceptions()) {
				throw new \RuntimeException($errmsg);
			}
			else {
				error_log($errmsg);
				return false;
			}
		}

		$file->write($this->_buildOutputString());

		$file->close();
	}

	/**
	 * Returns the iterator for this group.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_sections);
	}

	/**
	 * Returns the number of sections (implements the \Countable interface).
	 *
	 * @return integer The number of sections
	*/
	public function count()
	{
		return count($this->_sections);
	}

	/**
	 * Write the stored configuration to the last file successfully parsed
	 * in $this->read().
	 */
	public function save()
	{
		$file = end($this->_files);

		return $this->write($file->getPathname());
	}

    /**
     * Removes all parsed data.
     *
     * @return void
     */
	public function clear()
	{
		$this->_sections = array();
	}

	/**
	 * Output the current configuration representation.
	 *
	 * @return void
	 */
	public function dump()
	{
		var_dump($this->_sections);
	}

	/**
	 * Remove the specified section from the configuration. If the section in
	 * fact existed, return TRUE. Otherwise return FALSE.
	 */
	public function removeSection($section)
	{
		if (true === $this->hasSection($section)) {
			unset($this->_sections[$section]);
			return true;
		}
		else {
			return false;
		}
	}

    /**
     * Returns true if the section exists (implements the \ArrayAccess
     * interface).
     *
     * @param string $offset The name of the section
     *
     * @return Boolean true if the section exists, false otherwise
     */
    public function offsetExists($offset)
    {
        return $this->hasSection($offset);
    }

    /**
     * Returns the array of options associated with the section (implements
     * the \ArrayAccess interface).
     *
     * @param string $offset The offset of the value to get
     *
     * @return mixed The array of options associated with the section
     */
    public function offsetGet($offset)
    {
		return $this->hasSection($offset) ? $this->_sections[$offset] : null;
    }

    /**
     * Adds an array of options to the given section (implements the
     * \ArrayAccess interface).
     *
     * @param string $section The name of the section to insert $options.
     * @param array $options  The array of options to be added
     */
    public function offsetSet($offset, $value)
    {
		$this->_sections[$offset] = $value;
    }

	/**
	 * Removes the child with the given name from the form (implements the
	 * \ArrayAccess interface).
	 *
	 * @param string $name  The name of the child to be removed
	 */
	public function offsetUnset($name)
	{
		$this->remove($name);
	}

	protected function _throwExceptions()
	{
		return (false === $this->settings->get('throw_exceptions')) ? false : true;
	}
}

?>