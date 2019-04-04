<?php

declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Tests\Converter;

use PHPUnit\Framework\TestCase;
use Sculpin\Core\Converter\SourceConverterContext;
use Sculpin\Core\Source\SourceInterface;

class SourceConverterContextTest extends TestCase
{
    public function testContent()
    {
        $source = $this->createMock(SourceInterface::class);
        $source
            ->expects($this->once())
            ->method('content')
            ->will($this->returnValue('hello world'));

        $sourceConverterContext = new SourceConverterContext($source);

        $this->assertEquals('hello world', $sourceConverterContext->content());
    }

    public function testSetContent()
    {
        $source = $this->createMock(SourceInterface::class);
        $source
            ->expects($this->once())
            ->method('setContent')
            ->with($this->equalTo('hello world'));

        $sourceConverterContext = new SourceConverterContext($source);
        $sourceConverterContext->setContent('hello world');
    }
}
