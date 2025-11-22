<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional;

final class EventListenerExtensionTest extends FunctionalTestCase
{
    protected const PROJECT_DIR = '/__EventListenerFixture__';

    #[\Override]
    protected function setUp(): void
    {
        $outputDir = $this->projectDir() . '/output_test';
        if (self::$fs->exists($outputDir)) {
            self::$fs->remove($outputDir);
        }
    }

    public function testEventListenerExtensionBundle(): void
    {
        $expectedFile = 'sculpin.core.after_run.event';

        $this->assertProjectLacksFile('/output_test/' . $expectedFile);

        $this->executeSculpin(['generate']);

        $this->assertProjectHasGeneratedFile('/' . $expectedFile);
    }
}
