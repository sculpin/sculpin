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

namespace Sculpin\Core\Tests\Source\Map;

use Dflydev\DotAccessConfiguration\Configuration as Data;
use PHPUnit\Framework\TestCase;
use Sculpin\Core\Source\Map\CalculatedDateFromFilenameMap;
use Sculpin\Core\Source\MemorySource;

final class CalculatedDateFromFilenameMapTest extends TestCase
{
    private CalculatedDateFromFilenameMap $map;

    protected function setUp(): void
    {
        $this->map = new CalculatedDateFromFilenameMap();
    }

    /** @test */
    public function itShouldNotModifyAnExistingCalculatedDate(): void
    {
        $source = $this->getSourceWithCalculatedDate($timestamp = 123456);

        $this->map->process($source);

        $this->assertEquals($timestamp, $source->data()->get('calculated_date'));
    }

    /** @test */
    public function itShouldSetTheCalculatedDateIfFound(): void
    {
        $source = $this->getSourceWithoutCalculatedDateAndPathname("2013-12-12-sculpin-is-great.markdown");

        $this->map->process($source);

        $this->assertEquals(strtotime("2013-12-12"), $source->data()->get('calculated_date'));
    }

    /** @test */
    public function itShouldIncludeTheTimeIfFound(): void
    {
        $source = $this->getSourceWithoutCalculatedDateAndPathname("2013-12-12-220212-sculpin-is-great.markdown");

        $this->map->process($source);

        $this->assertEquals(strtotime("2013-12-12 22:02:12"), $source->data()->get('calculated_date'));
    }

    /** @test */
    public function itShouldIgnoreTheTimeIfItsProbablyNotATime(): void
    {
        $source = $this->getSourceWithoutCalculatedDateAndPathname(
            "2013-12-12-10-reasons-why-sculpin-is-great.markdown"
        );

        $this->map->process($source);

        $this->assertEquals(strtotime("2013-12-12"), $source->data()->get('calculated_date'));
    }

    private function getSourceWithCalculatedDate(int $timestamp): MemorySource
    {
        return new MemorySource(
            uniqid(),
            new Data(['calculated_date' => $timestamp]),
            "contents",
            "formatted contents",
            __FILE__,
            __FILE__,
            new \SplFileInfo(__FILE__),
            false,
            false,
            false
        );
    }

    private function getSourceWithoutCalculatedDateAndPathname(string $path): MemorySource
    {
        return new MemorySource(
            uniqid(),
            new Data(),
            "contents",
            "formatted contents",
            $path,
            $path,
            new \SplFileInfo(__FILE__),
            false,
            false,
            false
        );
    }
}
