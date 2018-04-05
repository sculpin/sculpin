<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional;

class GenerateCommandTest extends FunctionalTestCase
{
    /** @test */
    public function shouldGenerateInSpecifiedOutputDir()
    {
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/blog_index.html', '/source/index.html');
        $this->addProjectDirectory(__DIR__ . '/Fixture/source/_posts');

        $outputDir = 'custom_test_dir';
        $this->executeSculpin('generate --output-dir=' . $outputDir);

        $filePath = '/' . $outputDir . '/index.html';
        $msg      = "Expected project to have generated file at path $filePath.";

        $this->assertProjectHasFile($filePath, $msg);
    }
}
