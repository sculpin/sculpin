<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Tests\Source;

use Sculpin\Core\Source\ProxySource;

class ProxySourceTest extends \PHPUnit_Framework_TestCase
{
    public function testSetFormattedContent()
    {
        $source = $this->getMock('Sculpin\Core\Source\SourceInterface');
        $source
            ->expects($this->once())
            ->method('setFormattedContent')
            ->with($this->equalTo('hello world'));

        $SUT = new ProxySource($source);
        $SUT->setFormattedContent('hello world');
    }
}
