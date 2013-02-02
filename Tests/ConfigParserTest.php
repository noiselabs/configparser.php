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
    protected $fixturesDir;
    protected $outputFilename;

    protected function getFilename()
    {
        return $this->filenames[0];
    }

    protected function setUp()
    {
        $this->cfg = new ConfigParser();
        $this->fixturesDir = __DIR__.'/Fixtures';
        $this->filenames = array($this->fixturesDir.'/source.cfg');
        $this->outputFilename = tempnam(sys_get_temp_dir(), str_replace('\\', '_',__CLASS__).'_');
    }

    protected function tearDown()
    {
        file_exists($this->outputFilename) && unlink($this->outputFilename);
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

    public function testSupportedIniFileStructure()
    {
        $this->cfg->read($this->fixturesDir.'/supported_ini_file_structure.cfg');

        $section = 'Simple Values';
        $this->assertTrue($this->cfg->hasSection($section));
        $this->assertEquals($this->cfg->get($section, 'key'), 'value');
        $this->assertEquals($this->cfg->get($section, 'spaces in keys'), 'allowed');
        $this->assertEquals($this->cfg->get($section, 'spaces in values'), 'allowed as well');
        $this->assertEquals($this->cfg->get($section, 'spaces around the delimiter'), 'obviously');
        $this->assertEquals($this->cfg->get($section, 'you can also use'), 'to delimit keys from values');

        $section = 'All Values Are Strings';
        $this->assertTrue($this->cfg->hasSection($section));
        $this->assertEquals($this->cfg->get($section, 'values like this'), '1000000');
        $this->assertEquals($this->cfg->get($section, 'or this'), '3.14159265359');
        $this->assertEquals($this->cfg->get($section, 'are they treated as numbers?'), 'no');
        $this->assertEquals($this->cfg->get($section, 'integers, floats and booleans are held as'), 'strings');
        $this->assertEquals($this->cfg->get($section, 'can use the API to get converted values directly'), 'true');

        $section = 'Multiline Values';
        $this->assertTrue($this->cfg->hasSection($section));
        $this->assertEquals($this->cfg->get($section, 'chorus'), "I'm a lumberjack, and I'm okay");

        $section = 'No Values';
        $this->assertTrue($this->cfg->hasSection($section));
        $this->assertFalse($this->cfg->hasOption($section, 'key_without_value'));
        $this->assertEquals($this->cfg->get($section, 'empty string value here'), '');

        $section = 'You can use comments';
        $this->assertTrue($this->cfg->hasSection($section));

        $section = 'Sections Can Be Indented';
        $this->assertTrue($this->cfg->hasSection($section));
        $this->assertEquals($this->cfg->get($section, 'can_values_be_as_well'), 'True');
        $this->assertEquals($this->cfg->get($section, 'does_that_mean_anything_special'), 'False');
        $this->assertEquals($this->cfg->get($section, 'purpose'), 'formatting for readability');
        $this->assertEquals($this->cfg->get($section, 'multiline_values'), 'are');

        $section = 'issue #2';
        $this->assertEquals($this->cfg->get($section, 'subtitle'), 'test &amp');
        $this->assertEquals($this->cfg->get($section, 'escaped_subtitle'), 'test &amp; test');

    }
}
