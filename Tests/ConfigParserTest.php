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
 * Copyright (C) 2011-2013 Vítor Brandão <noisebleed@noiselabs.org>
 *
 *
 * @category    NoiseLabs
 * @package     ConfigParser
 * @author      Vítor Brandão <noisebleed@noiselabs.org>
 * @copyright   (C) 2011-2013 Vítor Brandão <noisebleed@noiselabs.org>
 */

namespace NoiseLabs\ToolKit\ConfigParser\Tests;

use NoiseLabs\ToolKit\ConfigParser\ConfigParser;

class ConfigParserTest extends \PHPUnit_Framework_TestCase
{
    protected $filenames;
    protected $out_filename;

    protected function getFilename()
    {
        return $this->filenames[0];
    }

    protected function setUp()
    {
        $this->cfg = new ConfigParser();
        $this->filenames = array(__DIR__.'/Fixtures/source.cfg');
        $this->out_filename = tempnam(sys_get_temp_dir(), str_replace('\\', '_',__CLASS__).'_');
    }

    protected function tearDown()
    {
        file_exists($this->out_filename) && unlink($this->out_filename);
    }

    /**
     * @expectedException \NoiseLabs\ToolKit\ConfigParser\Exception\DuplicateSectionException
     */
    public function testAddDuplicateSection()
    {
        $section = 'github.com';

        $this->cfg->read($this->getFilename());

        $this->cfg->addSection($section);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddNonStringSection()
    {
        $section = array();

        $this->cfg->read($this->getFilename());

        $this->cfg->addSection($section);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddDefaultSection()
    {
        $section = 'DeFaulT';

        $this->cfg->read($this->getFilename());

        $this->cfg->addSection($section);
    }

    public function testHasSection()
    {
        $this->cfg->read($this->getFilename());

        $this->assertFalse($this->cfg->hasSection('non-existing-section'));

        $this->assertFalse($this->cfg->hasSection('default'));

        $this->assertTrue($this->cfg->hasSection('github.com'));
    }

    public function testHasOption()
    {
        $this->cfg->read($this->getFilename());

        $this->assertTrue($this->cfg->hasOption('github.com', 'User'));

        $this->assertFalse($this->cfg->hasOption('non-existing-section', 'User'));

        $this->assertFalse($this->cfg->hasOption('github.com', 'non-existing-option'));

        $this->assertTrue($this->cfg->hasOption(null, 'ForwardX11'));

        $this->assertTrue($this->cfg->hasOption('', 'ForwardX11'));

        $this->assertFalse($this->cfg->hasOption('', 'User'));
    }
}
