<?php
/**
 * This file is part of NoiseLabs-PHP-ToolKit
 *
 * NoiseLabs-PHP-ToolKit is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * NoiseLabs-PHP-ToolKit is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with NoiseLabs-PHP-ToolKit; if not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Copyright (C) 2011-2013 Vítor Brandão <vitor@noiselabs.org>
 *
 *
 * @category    NoiseLabs
 * @package     ConfigParser
 * @copyright   (C) 2011-2013 Vítor Brandão <vitor@noiselabs.org>
 */

namespace NoiseLabs\ToolKit\ConfigParser;

/**
 * File.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class File
{
    /**
     * @var string
     * File pathname.
     */
    protected $path;

    /**
     * File pointer to the given filename.
     */
    protected $handle;

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
     * to modify the file, as using 'w' could truncate the file before the
     * lock was obtained (if truncation is desired, ftruncate() can be used
     * after the lock is requested).
     *
     * 'c+': Open the file for reading and writing; otherwise it has the
     * same behavior as 'c'.
     */
    protected $mode;

    /**
     * @var string
     * File contents.
     */
    protected $contents;

    /**
     * @var int
     * File modification time.
     */
    protected $mtime = -1;

    /**
     * Constructor.
     */
    public function __construct($filename, $mode = 'rb')
    {
        $this->path = $filename;
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getPathname()
    {
        return $this->path;
    }

    public function open($mode = null)
    {
        if (!isset($mode)) {
            $mode = $this->mode;
        }

        $this->handle = fopen($this->path, $mode);

        return $this->handle;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        if (!isset($this->handle) || !is_resource($this->handle)) {
            $this->open();
        }

        $mtime = filemtime($this->path);
        if (!isset($this->contents) || $mtime > $this->mtime) {
            $this->mtime = $mtime;
            $this->contents = fread($this->handle, filesize($this->path));
        }

        return $this->contents;
    }

    /**
     * @param  string $contents
     * @return string
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
    }

    public function write($content)
    {
        return ($this->handle) ? fwrite($this->handle, $content) : false;
    }

    /**
     * Closes the open file pointer.
     *
     * @return Returns TRUE on success or FALSE on failure.
     */
    public function close()
    {
        $rc = fclose($this->handle);
        $this->handle = null;

        return $rc;
    }

    /**
     * @return boolean
     */
    public function exists()
    {
        return file_exists($this->path);
    }

    public function isReadable()
    {
        return is_readable($this->path);
    }

    public function isWritable()
    {
        return is_writable($this->path);
    }

    public function remove()
    {
        return unlink($this->path);
    }
}
