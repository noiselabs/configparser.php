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

use NoiseLabs\ToolKit\ParameterBag;

abstract class BaseConfigParser implements \ArrayAccess, \IteratorAggregate, \Countable
{
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
	 *		Defaults to '\r\n' on Windows OS and '\n' or every other OS.
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
	 * Note the usage of INI_SCANNER_RAW to avoid parser_ini_files from
	 * parsing options and transforming 'false' values to empty strings.
	 */
	protected function _read($filename)
	{
		return parse_ini_file($filename, static::HAS_SECTIONS, INI_SCANNER_RAW);
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
			throw new \RuntimeException('File '.$file->getPathname().' could not be opened for writing');
			return false;
		}
		elseif (!$file->isWritable()) {
			throw new \RuntimeException('File '.$file->getPathname().' is not writable');
			return false;
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
}

?>