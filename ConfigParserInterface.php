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

/**
 * The Interface for the ConfigParser class.
 */
interface ConfigParserInterface extends \ArrayAccess, \Traversable, \Countable
{
	public function defaults();

	public function sections();

	public function addSection($section);

	public function hasSection($section);

	public function options($section);

	public function hasOption($section, $option);

	public function read($filenames = array());

	public function readFile($filehandler);

	public function readString($string);

	public function readArray(array $array);

	public function get($section, $option);

	public function getInt($section, $option);

	public function getFloat($section, $option);

	public function getBoolean($section, $option);

	public function set($section, $option, $value);

	public function write($filename = null, $space_around_delimiters = true);

	public function removeOption($section, $option);

	public function removeSection($section);

	public function dump();
}

?>