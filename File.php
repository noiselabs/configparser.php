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

class File
{
	protected $_path;

	/**
	 * File pointer to the given filename.
	 */
	protected $_handle;

	/**
	 * A list of possible modes for fopen():
	 *
	 * 'r': Open for reading only; place the file pointer at the beginning of
	 * the file.
	 *
	 * 'r+': Open for reading and writing; place the file pointer at the
	 * beginning of the file.
	 *
	 * 'w': Open for writing only; place the file pointer at the beginning of
	 * the file and truncate the file to zero length. If the file does not
	 * exist, attempt to create it.
	 *
	 * 'w+': Open for reading and writing; place the file pointer at the
	 * beginning of the file and truncate the file to zero length. If the file
	 * does not exist, attempt to create it.
	 *
	 * 'a': Open for writing only; place the file pointer at the end of the
	 * file. If the file does not exist, attempt to create it.
	 *
	 * 'a+': Open for reading and writing; place the file pointer at the end
	 * of the file. If the file does not exist, attempt to create it.
	 *
	 * 'x': Create and open for writing only; place the file pointer at the
	 * beginning of the file. If the file already exists, the fopen() call will
	 * fail by returning FALSE and generating an error of level E_WARNING. If
	 * the file does not exist, attempt to create it. This is equivalent to
	 * specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
	 *
	 * 'x+': Create and open for reading and writing; otherwise it has the
	 * same behavior as 'x'.
	 *
	 * 'c': Open the file for writing only. If the file does not exist, it is
	 * created. If it exists, it is neither truncated (as opposed to 'w'), nor
	 * the call to this function fails (as is the case with 'x'). The file
	 * pointer is positioned on the beginning of the file. This may be useful
	 * if it's desired to get an advisory lock (see flock()) before attempting
	 * to modify the file, as using 'w' could truncate the file before the lock
	 * was obtained (if truncation is desired, ftruncate() can be used after
	 * the lock is requested).
	 *
	 * 'c+': Open the file for reading and writing; otherwise it has the
	 * same behavior as 'c'.
	 */
	protected $_mode;

	public function __construct($filename, $mode = 'rb')
	{
		$this->_path = $filename;
		$this->_mode = $mode;
	}

	public function open()
	{
		$this->_handle = fopen($this->_path);

		return $this->_handle;
	}

	public function close()
	{
		return fclose($this->_handle);
	}
}

?>