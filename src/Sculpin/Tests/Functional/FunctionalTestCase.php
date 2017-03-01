<?php


namespace Sculpin\Tests\Functional;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This test case allows you to create a test project on the fly,
 * and run the sculpin binary against it. Test project files
 * will automatically be created and removed with every test.
 */
class FunctionalTestCase extends \PHPUnit_Framework_TestCase
{
    const PROJECT_DIR = '/__SculpinTestProject__';

    /** @var Filesystem */
    protected static $fs;

    public static function setUpBeforeClass()
    {
        self::$fs = new Filesystem();
    }

    public function setUp()
    {
        parent::setUp();

        $this->setUpTestProject();
    }

    protected function setUpTestProject()
    {
        $this->tearDownTestProject();

        $projectFiles = array(
            '/config/sculpin_kernel.yml',
            '/config/sculpin_site.yml',
            '/source/_layouts/raw.html.twig',
        );

        foreach ($projectFiles as $file) {
            $this->addProjectFile($file);
        }

        $this->writeToProjectFile('/source/_layouts/raw.html.twig', '{% block content %}{% endblock content %}');
    }

    protected function tearDownTestProject()
    {
        $projectDir = self::projectDir();
        if (self::$fs->exists($projectDir)) {
            self::$fs->remove($projectDir);
        }
    }

    /**
     * Execute a command against the sculpin binary
     * @param string $command
     */
    protected function executeSculpin($command)
    {
        $binPath = __DIR__ . '/../../../../bin';
        $projectDir = self::projectDir();
        exec("$binPath/sculpin $command --project-dir $projectDir --env=test");
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

        $projectDir = self::projectDir();

        if (!$recursive) {
            self::$fs->mkdir("$projectDir/$path");
            return;
        }

        $currPath = "$projectDir/";
        foreach ($pathParts as $dir) {
            $currPath .= "$dir/";
            if (!self::$fs->exists($currPath)) {
                self::$fs->mkdir($currPath);
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
        self::$fs->touch(self::projectDir() . $filePath);

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
        self::$fs->copy($fixturePath, self::projectDir() . $projectPath);
    }

    /**
     * @param string $filePath
     * @param null   $msg
     */
    protected function assertProjectHasFile($filePath, $msg = null)
    {
        $msg = $msg ?: "Expected project to contain file at path $filePath.";

        $this->assertTrue(self::$fs->exists(self::projectDir() . $filePath), $msg);
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
        self::$fs->dumpFile(self::projectDir() . $filePath, $content);
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
        return $this->crawlFile(self::projectDir() . $filePath);
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
        if (!self::$fs->exists($filePath)) {
            throw new \PHPUnit_Framework_Exception("Unable to read file at path $filePath: file does not exist");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \PHPUnit_Framework_Exception("Unable to read file at path $filePath: failed to read file.");
        }

        return $content;
    }

    /**
     * @return string
     */
    protected static function projectDir()
    {
        return __DIR__ . self::PROJECT_DIR;
    }
}
