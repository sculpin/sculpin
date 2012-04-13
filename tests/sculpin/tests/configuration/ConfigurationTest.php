<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\tests\configuration;

use sculpin\configuration\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected function getTestData()
    {
        return array(
            'a' => array(
                'b' => array(
                    'c' => 'ABC',
                ),
            ),
            'abc' => '%a.b.c%',
            'abcd' => '%a.b.c.d%',
            'some' => array(
                'object' => new ConfigurationTestObject('some.object'),
                'other' => array(
                    'object' => new ConfigurationTestObject('some.other.object'),
                ),
            ),
            'object' => new ConfigurationTestObject('object'),
        );
    }

    public function testGet()
    {
        $configuration = new Configuration($this->getTestData());

        $this->assertEquals('ABC', $configuration->get('a.b.c'), 'Direct access by dot notation');
        $this->assertEquals('ABC', $configuration->get('abc'), 'Resolved access');
        $this->assertEquals('%a.b.c.d%', $configuration->get('abcd'), 'Unresolved access');
        $this->assertEquals('object', $configuration->get('object')->key);
        $this->assertEquals('some.object', $configuration->get('some.object')->key);
        $this->assertEquals('some.other.object', $configuration->get('some.other.object')->key);
    }

    public function testExportRaw()
    {
        $configuration = new Configuration($this->getTestData());

        // Start with "known" expected value.
        $expected = $this->getTestData();

        $this->assertEquals($expected, $configuration->exportRaw());

        // Simulate change on an object to ensure that objects
        // are being handled correctly.
        $expected['object']->key = 'object (modified)';

        // Make the same change in the object that the
        // configuration is managing.
        $configuration->get('object')->key = 'object (modified)';

        $this->assertEquals($expected, $configuration->exportRaw());
    }

    public function testExport()
    {
        $configuration = new Configuration($this->getTestData());

        // Start with "known" expected value.
        $expected = $this->getTestData();

        // We have one replacement that is expected to happen.
        // It should be represented in the export as the
        // resolved value!
        $expected['abc'] = 'ABC';

        $this->assertEquals($expected, $configuration->export());

        // Simulate change on an object to ensure that objects
        // are being handled correctly.
        $expected['object']->key = 'object (modified)';

        // Make the same change in the object that the
        // configuration is managing.
        $configuration->get('object')->key = 'object (modified)';

        $this->assertEquals($expected, $configuration->export());
    }
}

class ConfigurationTestObject
{
    public $key;
    public function __construct($key)
    {
        $this->key = $key;
    }
}