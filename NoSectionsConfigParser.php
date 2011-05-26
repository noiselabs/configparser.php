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
		return isset($this->data[$option]);
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