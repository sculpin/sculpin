<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Contrib\Tests\ProxySourceCollection\Sorter;

use Sculpin\Contrib\ProxySourceCollection\ProxySourceItem;
use Sculpin\Contrib\ProxySourceCollection\Sorter\DefaultSorter;

class DefaultSorterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider sourcesProvider
     */
    public function testSort($a, $b, $expected)
    {
        $defaultSorter = new DefaultSorter();

        $this->assertEquals($expected, $defaultSorter->sort($a, $b));
    }

    public function sourcesProvider()
    {
        return array(
            array(
                new ProxySourceItemStub('title', '2014-01-01'),
                new ProxySourceItemStub('title', '2014-01-02'),
                1
            ),
            array(
                new ProxySourceItemStub('title', '2014-01-01'),
                new ProxySourceItemStub('title', '2014-01-01'),
                0,
            ),
            array(
                new ProxySourceItemStub('title', '2014-01-02'),
                new ProxySourceItemStub('title', '2014-01-01'),
                -1,
            ),
            array(
                new ProxySourceItemStub('title A', '2014-01-01'),
                new ProxySourceItemStub('title B', '2014-01-01'),
                1
            ),
            array(
                new ProxySourceItemStub('title A', '2014-01-01'),
                new ProxySourceItemStub('title A', '2014-01-01'),
                0,
            ),
            array(
                new ProxySourceItemStub('title B', '2014-01-01'),
                new ProxySourceItemStub('title A', '2014-01-01'),
                -1,
            ),
            array(
                new ProxySourceItemStub('', '', 'a'),
                new ProxySourceItemStub('', '', 'b'),
                1,
            ),
            array(
                new ProxySourceItemStub('', '', 'a'),
                new ProxySourceItemStub('', '', 'a'),
                0,
            ),
            array(
                new ProxySourceItemStub('', '', 'b'),
                new ProxySourceItemStub('', '', 'a'),
                -1,
            ),
        );
    }
}

class ProxySourceItemStub extends ProxySourceItem
{
    private $title;
    private $date;
    private $relativePathname;

    public function __construct($title, $date, $relativePathname = '')
    {
        $this->title = $title;
        $this->date = $date;
        $this->relativePathname = $relativePathname;
    }

    public function title()
    {
        return $this->title;
    }

    public function date()
    {
        return $this->date;
    }

    public function relativePathname()
    {
        return $this->relativePathname;
    }
}
