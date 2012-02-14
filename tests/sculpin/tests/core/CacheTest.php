<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\tests\core;

/**
 * These tests require the vfsStream mock filesystem driver
 * from https://github.com/mikey179/vfsStream/
 */
require_once 'vfsStream/vfsStream.php';

use sculpin\Sculpin;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->root = \vfsStream::setup('cacheRoot', null, array('bar' => array('a' => 'A', 'b' => 'B', 'c' => 'C')));
    }

    protected function sculpin($expectEarlyFailure = false)
    {
        $configuration = $this->getMock('sculpin\configuration\Configuration', array('get', 'getPath'), array(array()));
        $configuration
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap(array(
                array('exclude', array()),
                array('ignore', array()),
                array('raw', array()),
                array('project_ignore', array()),
                array('core_exclude', array()),
                array('core_project_ignore', array()),
                array('source_is_project_root', false),
            )));
        if (!$expectEarlyFailure) {
            $configuration
                ->expects($this->once())
                ->method('getPath')
                ->with('cache')
                ->will($this->returnValue(\vfsStream::url('cacheRoot')));
        }
        return new Sculpin($configuration);
    }

    public function testPrepareCacheFor()
    {
        $this->assertFalse($this->root->hasChild('foo'), 'The "foo" child should not exist yet');
        $this->sculpin()->prepareCacheFor('foo');
        $this->assertTrue($this->root->hasChild('foo'), 'The "foo" child should now exist');
    }

    public function testPrepareCacheForNullDirectory()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->sculpin(true)->prepareCacheFor(null);
    }

    public function testPrepareCacheForEmptyStringDirectory()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->sculpin(true)->prepareCacheFor('');
    }

    public function testClearCacheFor()
    {
        $this->assertTrue($this->root->hasChild('bar'), 'The "bar" child should exist');
        $this->assertTrue($this->root->getChild('bar')->hasChild('a'), 'The "a" child of "bar" should exist');
        $this->sculpin()->clearCacheFor('bar');
        $this->assertTrue($this->root->hasChild('bar'), 'The "bar" child should still existing');
        $this->assertFalse($this->root->getChild('bar')->hasChild('a'), 'The "a" child of "bar" should no longer exist');
    }

    public function testClearCache()
    {
        $this->assertTrue($this->root->hasChild('bar'), 'The "bar" child should exist');
        $this->assertTrue($this->root->getChild('bar')->hasChild('a'), 'The "a" child of "bar" should exist');
        $this->sculpin()->clearCache();
        $this->assertFalse($this->root->hasChild('bar'), 'The "bar" child should no longer exist');
    }

    public function testClearCacheForNullDirectory()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->sculpin(true)->clearCacheFor(null);
    }

    public function testClearCacheForEmptyStringDirectory()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->sculpin(true)->clearCacheFor('');
    }
}
