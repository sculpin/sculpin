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

use sculpin\configuration\YamlFileConfigurationBuilder;

class YamlFileConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuilder()
    {
        $configurationBuilder = new YamlFileConfigurationBuilder(array(__DIR__.'/fixtures/yamlFileConfigurationBuilderTest-testBuilder.yml'));

        $configuration = $configurationBuilder->build();

        $this->assertEquals('C', $configuration->get('a.b.c'));
        $this->assertEquals('C0', $configuration->get('a0.b0.c0'));
        $this->assertEquals('C1', $configuration->get('a1.b1.c1'));
        $this->assertEquals(array(
            'yamlFileConfigurationBuilderTest-testBuilder-import-level0.yml',
            'yamlFileConfigurationBuilderTest-testBuilder-import-level1.yml',
        ), $configuration->get('imports'));
    }
}