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
            'abc' => '${a.b.c}',
            'abcd' => '${a.b.c.d}',
        );
    }

    public function testGet()
    {
        $configuration = new Configuration($this->getTestData());

        $this->assertEquals('ABC', $configuration->get('a.b.c'), 'Direct access by dot notation');
        $this->assertEquals('ABC', $configuration->get('abc'), 'Resolved access');
        $this->assertEquals('${a.b.c.d}', $configuration->get('abcd'), 'Unresolved access');
    }
}