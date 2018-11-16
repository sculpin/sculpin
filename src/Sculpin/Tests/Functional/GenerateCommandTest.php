<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional;

class GenerateCommandTest extends FunctionalTestCase
{
    /** @test */
    public function shouldGenerateInSpecifiedOutputDir(): void
    {
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/blog_index.html', '/source/index.html');
        $this->addProjectDirectory(__DIR__ . '/Fixture/source/_posts');

        $outputDir = 'custom_test_dir';
        $this->executeSculpin('generate --output-dir=' . $outputDir);

        $filePath = '/' . $outputDir . '/index.html';
        $msg      = "Expected project to have generated file at path $filePath.";

        $this->assertProjectHasFile($filePath, $msg);
    }

    /** @test */
    public function shouldGenerateUsingSpecifiedSourceDir(): void
    {
        $filePath  = '/output_test/index.html';
        $sourceDir = 'custom_source_dir';

        $this->assertProjectLacksFile($filePath, "Expected project to NOT have generated file at path $filePath.");

        // set up test scenario
        static::$fs->rename(
            $this->projectDir() . '/source',
            $this->projectDir() . '/' . $sourceDir
        );
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/blog_index.html', '/'. $sourceDir .'/index.html');
        $this->addProjectDirectory('/' . $sourceDir . '/_posts');

        // generate the site
        $this->executeSculpin('generate --source-dir=' . $sourceDir);

        // check that it worked
        $this->assertProjectHasFile($filePath, "Expected project to have generated file at path $filePath.");
    }
}
