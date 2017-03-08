<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Tests\Formatter;

use Sculpin\Core\Formatter\FormatContext;

class FormatContextTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $formatContext = new FormatContext('someTemplateId', 'template text', array(
            'a' => 'Some A Value',
            'formatter' => 'SOME_FORMATTER',
        ));

        $this->assertEquals('someTemplateId', $formatContext->templateId());
        $this->assertEquals('template text', $formatContext->template());
        $this->assertEquals(
            array('a' => 'Some A Value', 'formatter' => 'SOME_FORMATTER', ),
            $formatContext->data()->export()
        );
        $this->assertEquals('SOME_FORMATTER', $formatContext->formatter());
    }
}
