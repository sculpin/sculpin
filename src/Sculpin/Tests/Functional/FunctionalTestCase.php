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
    protected const PROJECT_DIR = '/__SculpinTestProject__';

    /** @var Filesystem */
    protected static $fs;

    public static function setUpBeforeClass()
    {
        static::$fs = new Filesystem();
    }

    public function setUp()
    {
        parent::setUp();

        $this->setUpTestProject();
    }

    protected function setUpTestProject()
    {
        $this->tearDownTestProject();

        $projectFiles = [
            '/config/sculpin_kernel.yml',
            '/config/sculpin_site.yml',
            '/source/_layouts/raw.html.twig',
        ];

        foreach ($projectFiles as $file) {
            $this->addProjectFile($file);
        }

        $this->writeToProjectFile('/source/_layouts/raw.html.twig', '{% block content %}{% endblock content %}');
    }

    protected function tearDownTestProject()
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
    protected function executeSculpin($command)
    {
        $binPath    = __DIR__ . '/../../../../bin';
        $projectDir = static::projectDir();
        exec("$binPath/sculpin $command --project-dir $projectDir --env=test");
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
    protected function executeSculpinAsync($command, $start = true, callable $callback = null)
    {
        $binPath    = __DIR__ . '/../../../../bin';
        $projectDir = static::projectDir();
        $process    = new Process("$binPath/sculpin $command --project-dir $projectDir --env=test");

        if ($start) {
            $process->start($callback);
        }

        return $process;
    }

    /**
     * @param string $path
     * @param bool $recursive
     */
    protected function addProjectDirectory($path, $recursive = true)
    {
        $pathParts = explode('/', $path);
        // Remove leading slash
        array_shift($pathParts);

        $projectDir = static::projectDir();

        if (!$recursive) {
            static::$fs->mkdir("$projectDir/$path");
            return;
        }

        $currPath = "$projectDir/";
        foreach ($pathParts as $dir) {
            $currPath .= "$dir/";
            if (!static::$fs->exists($currPath)) {
                static::$fs->mkdir($currPath);
            }
        }
    }

    /**
     * @param string $filePath
     * @param string $content
     */
    protected function addProjectFile($filePath, $content = null)
    {
        $dirPathParts = explode('/', $filePath);
        // Remove leading slash
        array_shift($dirPathParts);
        // Remove file name
        array_pop($dirPathParts);

        // Add the file directories
        $hasDirectoryPath = !empty($dirPathParts);
        if ($hasDirectoryPath) {
            $dirPath = '/' . join('/', $dirPathParts);
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
    protected function copyFixtureToProject($fixturePath, $projectPath)
    {
        static::$fs->copy($fixturePath, static::projectDir() . $projectPath);
    }

    /**
     * @param string $filePath
     * @param null   $msg
     */
    protected function assertProjectHasFile($filePath, $msg = null)
    {
        $msg = $msg ?: "Expected project to contain file at path $filePath.";

        $this->assertTrue(static::$fs->exists(static::projectDir() . $filePath), $msg);
    }

    /**
     * @param string $filePath
     * @param string $msg
     */
    protected function assertProjectHasGeneratedFile($filePath, $msg = null)
    {
        $outputDir = '/output_test';

        $msg = $msg ?: "Expected project to have generated file at path $filePath.";
        $this->assertProjectHasFile($outputDir . $filePath, $msg);
    }

    /**
     * @param string $filePath
     * @param string $content
     */
    protected function writeToProjectFile($filePath, $content)
    {
        static::$fs->dumpFile(static::projectDir() . $filePath, $content);
    }

    /**
     * @param string $filePath
     * @return Crawler
     */
    protected function crawlGeneratedProjectFile($filePath)
    {
        return $this->crawlProjectFile('/output_test' . $filePath);
    }

    /**
     * @param string $filePath
     * @return Crawler
     */
    protected function crawlProjectFile($filePath)
    {
        return $this->crawlFile(static::projectDir() . $filePath);
    }

    /**
     * @param string $filePath
     * @return Crawler
     */
    private function crawlFile($filePath)
    {
        $content = $this->readFile($filePath);

        return new Crawler($content);
    }

    /**
     * @param $filePath
     * @return string
     */
    private function readFile($filePath)
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
    protected static function projectDir()
    {
        return __DIR__ . static::PROJECT_DIR;
    }
}
