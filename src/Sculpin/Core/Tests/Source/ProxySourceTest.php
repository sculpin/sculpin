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

namespace Sculpin\Core\Tests\Source;

use PHPUnit\Framework\TestCase;
use Sculpin\Core\Source\ProxySource;
use Sculpin\Core\Source\SourceInterface;

class ProxySourceTest extends TestCase
{
    public function testSetFormattedContent()
    {
        $source = $this->createMock(SourceInterface::class);
        $source
            ->expects($this->once())
            ->method('setFormattedContent')
            ->with($this->equalTo('hello world'));

        $SUT = new ProxySource($source);
        $SUT->setFormattedContent('hello world');
    }
}
