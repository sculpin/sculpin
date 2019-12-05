<?php

declare(strict_types=1);


namespace Sculpin\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * This test case allows you to create a test project on the fly,
 * and run the sculpin binary against it. Test project files
 * will automatically be created and removed with every test.
 */
class FunctionalTestCase extends TestCase
{
    protected const PROJECT_DIR = DIRECTORY_SEPARATOR . '__SculpinTestProject__';

    /** @var Filesystem */
    protected static $fs;

    /** @var string */
    protected $executeOutput;

    public static function setUpBeforeClass(): void
    {
        static::$fs = new Filesystem();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpTestProject();
    }

    protected function setUpTestProject(): void
    {
        $this->tearDownTestProject();

        $projectFiles = [
            DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'sculpin_kernel.yml',
            DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'sculpin_site.yml',
            DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR . '_layouts' . DIRECTORY_SEPARATOR . 'default.html.twig',
            DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR . '_layouts' . DIRECTORY_SEPARATOR . 'raw.html.twig',
        ];

        foreach ($projectFiles as $file) {
            $this->addProjectFile($file);
        }

        $this->writeToProjectFile(DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR . '_layouts' . DIRECTORY_SEPARATOR . 'default.html.twig', '{% block content %}{% endblock content %}');
        $this->writeToProjectFile(
            DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR . '_layouts' . DIRECTORY_SEPARATOR . 'raw.html.twig',
            '{% extends "default" %}{% block content %}{% endblock content %}'
        );
    }

    protected function tearDownTestProject(): void
    {
        $projectDir = static::projectDir();
        if (static::$fs->exists($projectDir)) {
            static::$fs->remove($projectDir);
        }
    }

    /**
     * Execute a command against the sculpin binary
     * @param string $command
     */
    protected function executeSculpin($command): void
    {
        $binPath    = __DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'bin';
        $projectDir = static::projectDir();
        exec(
            "$binPath" . DIRECTORY_SEPARATOR . "sculpin $command --project-dir $projectDir --env=test",
            $this->executeOutput
        );
    }

    /**
     * Asynchronously execute a command against the sculpin binary
     *
     * Remember to stop the process when finished!
     *
     * @param string   $command
     * @param bool     $start     Default: start the process right away
     * @param callable $callback
     *
     * @return Process
     */
    protected function executeSculpinAsync(string $command, bool $start = true, ?callable $callback = null): Process
    {
        $binPath    = __DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'bin';
        $projectDir = static::projectDir();
        $process    = new Process("$binPath" . DIRECTORY_SEPARATOR . "sculpin $command --project-dir $projectDir --env=test");

        if ($start) {
            $process->start($callback);
        }

        return $process;
    }

    /**
     * @param string $path
     * @param bool $recursive
     */
    protected function addProjectDirectory(string $path, bool $recursive = true): void
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $path);
        // Remove leading slash
        array_shift($pathParts);

        $projectDir = static::projectDir();

        if (!$recursive) {
            static::$fs->mkdir("$projectDir" . DIRECTORY_SEPARATOR . "$path");
            return;
        }

        $currPath = "$projectDir" . DIRECTORY_SEPARATOR;
        foreach ($pathParts as $dir) {
            $currPath .= "$dir" . DIRECTORY_SEPARATOR;
            if (!static::$fs->exists($currPath)) {
                static::$fs->mkdir($currPath);
            }
        }
    }

    /**
     * @param string $filePath
     * @param string $content
     */
    protected function addProjectFile(string $filePath, ?string $content = null): void
    {
        $dirPathParts = explode(DIRECTORY_SEPARATOR, $filePath);
        // Remove leading slash
        array_shift($dirPathParts);
        // Remove file name
        array_pop($dirPathParts);

        // Add the file directories
        $hasDirectoryPath = !empty($dirPathParts);
        if ($hasDirectoryPath) {
            $dirPath = DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $dirPathParts);
            $this->addProjectDirectory($dirPath);
        }

        // Create the file
        static::$fs->touch(static::projectDir() . $filePath);

        // Add content to the file
        if (!is_null($content)) {
            $this->writeToProjectFile($filePath, $content);
        }
    }

    /**
     * @param string $fixturePath
     * @param string $projectPath
     */
    protected function copyFixtureToProject(string $fixturePath, string $projectPath): void
    {
        static::$fs->copy($fixturePath, static::projectDir() . $projectPath);
    }

    /**
     * @param string        $filePath
     * @param string|null   $msg
     */
    protected function assertProjectHasFile(string $filePath, ?string $msg = null): void
    {
        $msg = $msg ?: "Expected project to contain file at path $filePath.";

        $this->assertTrue(static::$fs->exists(static::projectDir() . $filePath), $msg);
    }

    /**
     * @param string        $filePath
     * @param string|null   $msg
     */
    protected function assertProjectLacksFile(string $filePath, ?string $msg = null): void
    {
        $msg = $msg ?: "Expected project to NOT contain file at path $filePath.";

        $this->assertFalse(static::$fs->exists(static::projectDir() . $filePath), $msg);
    }

    /**
     * @param string $filePath
     * @param string|null $msg
     */
    protected function assertProjectHasGeneratedFile(string $filePath, ?string $msg = null): void
    {
        $outputDir = DIRECTORY_SEPARATOR . 'output_test';

        $msg = $msg ?: "Expected project to have generated file at path $filePath.";
        $this->assertProjectHasFile($outputDir . $filePath, $msg);
    }

    /**
     * @param string $filePath
     * @param string $expected
     * @param string|null $msg
     */
    protected function assertGeneratedFileHasContent(string $filePath, string $expected, ?string $msg = null): void
    {
        $outputDir = DIRECTORY_SEPARATOR . 'output_test';

        $msg        = $msg ?: "Expected generated file at path $filePath to have content '$expected'.";
        $fullPath   = static::projectDir() . $outputDir . $filePath;
        $fileExists = static::$fs->exists($fullPath);

        $this->assertTrue($fileExists, $msg . ' (File Not Found!)');

        $contents = file_get_contents($fullPath);
        $this->assertContains($expected, $contents, $msg);
    }

    /**
     * @param string $filePath
     * @param string $content
     */
    protected function writeToProjectFile(string $filePath, string $content): void
    {
        static::$fs->dumpFile(static::projectDir() . $filePath, $content);
    }

    /**
     * @param string $filePath
     * @return Crawler
     */
    protected function crawlGeneratedProjectFile(string $filePath): Crawler
    {
        return $this->crawlProjectFile(DIRECTORY_SEPARATOR . 'output_test' . $filePath);
    }

    /**
     * @param string $filePath
     * @return Crawler
     */
    protected function crawlProjectFile(string $filePath): Crawler
    {
        return $this->crawlFile(static::projectDir() . $filePath);
    }

    /**
     * @param string $filePath
     * @return Crawler
     */
    private function crawlFile(string $filePath): Crawler
    {
        $content = $this->readFile($filePath);

        return new Crawler($content);
    }

    /**
     * @param $filePath
     * @return string
     */
    private function readFile(string $filePath): string
    {
        if (!static::$fs->exists($filePath)) {
            throw new \PHPUnit\Framework\Exception("Unable to read file at path $filePath: file does not exist");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \PHPUnit\Framework\Exception("Unable to read file at path $filePath: failed to read file.");
        }

        return $content;
    }

    /**
     * @return string
     */
    protected static function projectDir(): string
    {
        return __DIR__ . static::PROJECT_DIR;
    }
}
