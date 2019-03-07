<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional;

use Symfony\Component\Finder\Finder;

class EventListenerExtensionTest extends FunctionalTestCase
{
    protected const PROJECT_DIR = '/__EventListenerFixture__';

    public function setUp(): void
    {
        $outputDir = $this->projectDir() . '/output_test';
        if (static::$fs->exists($outputDir)) {
            static::$fs->remove($outputDir);
        }
    }

    public function testEventListenerExtensionBundle(): void
    {
        $expectedFile = 'sculpin.core.after_run.event';

        $this->assertProjectLacksFile('/output_test/' . $expectedFile);

        $this->executeSculpin('generate');

        $this->assertProjectHasGeneratedFile('/' . $expectedFile);
    }
}
