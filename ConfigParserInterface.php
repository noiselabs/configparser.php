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

    public function addSection(string $section);

    public function hasSection(string $section);

    public function options(string $section);

    public function hasOption(?string $section, string $option);

    public function read($filenames = []);

    public function readFile($filehandler);

    public function readString(string $string);

    public function readArray(array $array);

    public function get(string $section, string $option);

    public function getInt(string $section, string $option);

    public function getFloat(string $section, string $option);

    public function getBoolean(string $section, string $option);

    public function set(string $section, string $option, $value);

    public function write(string $filename);

    public function save();

    public function removeOption(string $section, string $option);

    public function removeSection(string $section);

    public function dump();
}
