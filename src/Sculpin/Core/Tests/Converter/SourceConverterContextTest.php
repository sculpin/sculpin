<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Tests\Converter;

use Sculpin\Core\Converter\SourceConverterContext;
use Sculpin\Core\Source\SourceInterface;

/**
 * SourceConverterContext Test
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SourceConverterContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test content() method.
     */
    public function testContent()
    {
        $source = $this->getMock('Sculpin\Core\Source\SourceInterface');
        $source
            ->expects($this->once())
            ->method('content')
            ->will($this->returnValue('hello world'));

        $sourceConverterContext = new SourceConverterContext($source);

        $this->assertEquals('hello world', $sourceConverterContext->content());
    }

    /**
     * Test setContent() method.
     */
    public function testSetContent()
    {
        $source = $this->getMock('Sculpin\Core\Source\SourceInterface');
        $source
            ->expects($this->once())
            ->method('setContent')
            ->with($this->equalTo('hello world'));

        $sourceConverterContext = new SourceConverterContext($source);
        $response = $sourceConverterContext->setContent('hello world');
    }
}
