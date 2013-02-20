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
 * Copyright (C) 2011 Vítor Brandão <vitor@noiselabs.org>
 *
 *
 * @category NoiseLabs
 * @package ConfigParser
 * @version 0.1.1
 * @author Vítor Brandão <vitor@noiselabs.org>
 * @copyright (C) 2011 Vítor Brandão <vitor@noiselabs.org>
 */

namespace NoiseLabs\ToolKit\ConfigParser;

/**
 * The Interface for the ConfigParser class.
 */
interface ConfigParserInterface
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

    public function write($filename);

    public function save();

    public function removeOption($section, $option);

    public function removeSection($section);

    public function dump();
}
