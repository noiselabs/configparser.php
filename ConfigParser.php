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
 */
Class ConfigParser implements \IteratorAggregate, ConfigParserInterface
{
	const DEFAULT_SECTION 	= 'DEFAULT';
	const HAS_SECTIONS 		= true;

	/**
	 * The configuration representation is stored here.
	 * @var array
	 */
	private $_data = array();

	private $_defaults = array();

	/**
	 * An array of FILE objects representing the loaded files.
	 * @var array
	 */
	private $_files = array();

	public function __construct(array $defaults = array())
	{
		$this->_defaults = $defaults;
	}

	// from child class
  	public static function getConfig($filename, $has_sections = true, $safemode = false)
  	{
		$cp = new static();
		$data = $cp->read($filename);

    	//$_instance = new self($filename, $has_sections, $safemode);
    	//$_instance->read();

    	//return $_instance;
  	}

  	/* Do not allow an explicit call of the constructor: $c1 = new ConfigParser(); */
  	/*public function __construct($filename, $has_sections, $safemode)
  	{
		$path_to_etc = Application::getInstance()->getConfig()->get('path.etc');

    	$this->_has_sections = $has_sections;
    	$this->safemode = $safemode;

    	if ($filename[0] == '/') {
      		$this->_filepath = $filename;
    	}
    	else {
    		$this->_filepath = sprintf("%s/%s", $path_to_etc, $filename);
    	}
  	}*/

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
		return array_keys($this->_data);
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
			$this->_data[(string) $section] = array();
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
		return isset($this->_data[$section]);
	}

	/**
	 * Return a list of options available in the specified section.
	 */
	public function options($section)
	{
		if (true === $this->hasSection($section)) {
			return array_keys($this->_data[$section]);
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
		return isset($this->_data[$section][$option]);
	}

	/**
	 * @throws NoSectionException if section doesn't exist
	 */
	public function setOptions($section, array $options = array())
	{
		if ($this->hasSection($section)) {
			$this->_data[$section] = $options;
		}
		else {
			throw new NoSectionException($section);
		}
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
				$this->_data = array_merge(
								$this->_data,
								parse_ini_file($filename, static::HAS_SECTIONS)
					);
			}
		}
	}

	public function readFile($filehandler)
	{
	}

	public function readString($string)
	{
	}

	public function readArray(array $array)
	{
	}

	/**
	 * Get an option value for the named section.
	 */
	public function get($section, $option)
	{
		if ($this->hasOption($section, $option)) {
			return $this->_data[$section][$option];
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
			$this->_data[$section][$option] = (string) $value;
		}
		else {
			throw new NoSectionException($section);
		}
	}

	public function write($filename = NULL, $space_around_delimiters = true)
	{
	}

	/**
	 * Remove the specified option from the specified section. If the section
	 * does not exist, raise NoSectionException. If the option existed to be
	 * removed, return TRUE; otherwise return FALSE.
	 */
	public function removeOption($section, $option)
	{
		if (true === $this->hasSection($section)) {
			if (isset($this->_data[$section][$option])) {
				unset($this->_data[$section][$option]);
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

	/**
	 * Remove the specified section from the configuration. If the section in
	 * fact existed, return TRUE. Otherwise return FALSE.
	 */
	public function removeSection($section)
	{
		if (true === $this->hasSection($section)) {
			unset($this->_data[$section]);
			return true;
		}
		else {
			return false;
		}
	}

	public function read_($filenames = array())
 	{
		if (!is_array($filenames)) {
			$filenames = array($filenames);
		}

    	if (is_readable($this->_filepath)) {
      		$this->_cfg = parse_ini_file($this->_filepath, $this->_has_sections);
    	}
  	}

  	/**
   	 * Get an option value for the named section.
   	 */
  	public function get_($section, $option=NULL)
  	{
    	if ($this->_has_sections)
    	{
      		if ($this->safemode)
      		{
        		if ( array_key_exists($section, $this->_cfg) &&
            		array_key_exists($option, $this->_cfg[$section]) )
            	{
          			return $this->_cfg[$section][$option];
        		}
        		else
        		{
          			return false;
        		}
      		}
      		else
      		{
        		return $this->_cfg[$section][$option];
      		}
    	}
    	else
    	{
      		// When configured with "no sections" the section becomes the option
      		$option = $section;

      		if ($this->safemode)
      		{
        		if (array_key_exists($option, $this->_cfg))
        		{
          			return $this->_cfg[$option];
        		}
        		else
        		{
          			return false;
        		}
      		}
      		else
      		{
        		return $this->_cfg[$option];
      		}
    	}
  	}

  /**
   * Prints all available sections
   */
  public function sections_() {
    if (!empty($this->_cfg)) {
      foreach ($section as $this->_cfg) {
        $this->_sections[] = $section;
      }
    }
    return $this->_sections;
  }

  /**
   * Indicates whether the named section is present in the configuration.
   */
  public function has_section($section) {
    if (array_key_exists($section, $this->_cfg)) {
      return True;
    }
    else {
      return False;
    }
  }

  /**
   * If the given section exists, and contains the given option, return True;
   * otherwise return False.
   */
  public function has_option($section, $option=NULL) {
    if ( array_key_exists($section, $this->_cfg) &&
           array_key_exists($option, $this->_cfg[$section]) ) {
        return True;
    }
    else {
      return False;
    }
  }

  public function set_($section, $option, $value=NULL) {
    if ($this->_has_sections) {
      if (!array_key_exists($section, $this->_cfg)) {
        $this->_cfg[] = $section;
      }
      $this->_cfg[$section][$option] = $value;
    }
    else {
      // "shift" arguments
      // Note: this is *really* lame
      $value = $option;
      $option = $section;
      $this->_cfg[$option] = $value;
    }
  }

	/**
	 * Write a representation of the configuration to the specified
	 * filename. This representation can be parsed by a future read() call.
	 *
	 * If $space_around_delimiters is true, delimiters between keys and
	 * values are surrounded by spaces.
	 *
	 * When $filename is NULL, the last file loaded (if any) is used.
	 */
	public function write_($filename = NULL, $space_around_delimiters = true)
	{

    if ($this->_has_sections) {
        foreach ($this->_cfg as $key=>$elem) {
            $content .= "\n[".$key."]\n";
            foreach ($elem as $key2=>$elem2) {
                if(is_array($elem2))
                {
                    for($i=0;$i<count($elem2);$i++)
                    {
                        $content .= $key2."[] = ".$elem2[$i]."\n";
                    }
                }
                else if($elem2=="") $content .= $key2." = \n";
                else $content .= $key2." = ".$elem2."\n";
            }
        }
    }
    else {
        foreach ($this->_cfg as $key=>$elem) {
            if (is_array($elem)) {
                for($i=0;$i<count($elem);$i++) {
                    $content .= $key."[] = ".$elem[$i]."\n";
                }
            }
            else {
              if ($elem=="") {
                $content .= $key." = \n";
              }
              else {
                $content .= $key." = ".$elem."\n";
              }
            }
        }
    }

    if (!$handle = fopen($this->_filepath, 'w')) {
        return false;
    }
    if (!fwrite($handle, $content)) {
        return false;
    }
    fclose($handle);
    return true;
  }

	/**
	 * Output the current configuration representation.
	 */
	public function dump()
	{
		var_dump($this->_data);
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
		return $this->hasSection($offset) ? $this->_data[$offset] : null;
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
		// add the given section if it doesn't exist yet
		if (!$this->hasSection($offset)) {
			$this->addSection($offset);
		}

		$this->setOptions($offset, $value);
    }

    /**
     * Removes the child with the given name from the form (implements the \ArrayAccess interface).
     *
     * @param string $name  The name of the child to be removed
     */
    public function offsetUnset($name)
    {
        $this->remove($name);
    }

    /**
     * Returns the iterator for this group.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_data);
    }

    /**
     * Returns the number of sections (implements the \Countable interface).
     *
     * @return integer The number of sections
     */
    public function count()
    {
        return count($this->_data);
    }
}

?>