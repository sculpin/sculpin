<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional;

class GenerateCommandTest extends FunctionalTestCase
{
    public const CONFIG_FILE = DIRECTORY_SEPARATOR . 'app'
        . DIRECTORY_SEPARATOR . 'config'
        . DIRECTORY_SEPARATOR . 'sculpin_kernel.yml';

    public function tearDown(): void
    {
        parent::tearDown();

        $this->writeToProjectFile(self::CONFIG_FILE, '');
    }

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

    /** @test */
    public function shouldExposeWebpackManifestInTwig(): void
    {
        $this->configureForWebpack();
        $this->copyFixtureToProject(__DIR__ . '/Fixture/webpack_manifest/manifest_test.md', '/source/test.md');
        $this->copyFixtureToProject(__DIR__ . '/Fixture/webpack_manifest/manifest.json', '/source/build/manifest.json');

        $this->executeSculpin('generate');

        $filePath = '/test/index.html';
        $msg      = "Expected project to have generated file at path $filePath.";

        $this->assertProjectHasFile('/output_test' . $filePath, $msg);
        $this->assertGeneratedFileHasContent($filePath, 'Testing CSS /build/css/app.9141cd43.css');
        $this->assertGeneratedFileHasContent($filePath, 'Testing JS /build/js/app.43dcc737.js');
    }

    protected function configureForWebpack(): void
    {
        $this->writeToProjectFile(
            self::CONFIG_FILE,
            'sculpin_twig:' . "\n"
            . '    webpack_manifest: build/manifest.json' . "\n"
        );
    }
}
