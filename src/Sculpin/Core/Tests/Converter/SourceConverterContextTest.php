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

use Sculpin\Core\Tests\Base;
use Sculpin\Core\Converter\SourceConverterContext;
use Sculpin\Core\Source\SourceInterface;
use \PHPUnit_Framework_MockObject_MockObject;

class SourceConverterContextTest extends Base
{
    public function testSourceConverterContextReturnsExpectedOutput()
    {
        /** @var $source PHPUnit_Framework_MockObject_MockObject|SourceInterface */
        $source = $this
            ->getMockBuilder('Sculpin\Core\Source\SourceInterface')
            ->getMock();

        $source
            ->expects($this->once())
            ->method('content')
            ->will($this->returnValue('hello world'));

        $sourceConverterContext = new SourceConverterContext($source);

        $this->assertEquals('hello world', $sourceConverterContext->content());
    }
}
