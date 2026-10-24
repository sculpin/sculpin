<?php

namespace Functional;

use Sculpin\Tests\Functional\FunctionalTestCase;

class ComplexProviderLifecycleTest extends FunctionalTestCase
{
    protected const string PROJECT_DIR = '/__ComplexProviderLifecycleFixture__';

    #[\Override]
    protected function setUp(): void
    {
        $outputDir = $this->projectDir() . '/output_test';
        if (self::$fs->exists($outputDir)) {
            self::$fs->remove($outputDir);
        }
    }

    public function testComplexLifecycleBuildsProperly(): void
    {
        $expectedFile = 'index.html';
        $expectedFileContents = '<strong>hypothetical</strong>';

        $this->assertProjectLacksFile('/output_test/' . $expectedFile);

        $this->executeSculpin(['generate']);

        $this->assertProjectHasGeneratedFile('/' . $expectedFile);

        // @todo This assertion will fail, demonstrating a lifecycle problem: the inner
        //       include's content blocks are not rendered.
        $this->assertGeneratedFileHasContent('/' . $expectedFile, $expectedFileContents);
    }

    /** @test */
    public function testComplexLifecycleBuildsProperly_WhileWatching(): void
    {
        $sourcePage = __DIR__ . self::PROJECT_DIR . '/source/_clades/1-jacksons.html';
        $generatedPage = __DIR__ . self::PROJECT_DIR . '/' . 'output_test/index.html';
        $expectedFileContents = '<strong>hypothetical</strong>';
        $matchString = "{% include 'jackson.html' %}";

        // ensure consistent test state
        $rawPage = file_get_contents($sourcePage);
        $this->modifySourcePage(
            filePath: $sourcePage,
            regex: '# *' . $matchString . ' *#',
            replacement: $matchString,
            body: $rawPage
        );

        // start our async sculpin watcher/server
        $process = $this->executeSculpinAsync(['generate', '--watch']);

        sleep(1); // wait until our file exists
        $pageContent = file_get_contents($generatedPage);

        // check for the word being tested
        // @todo If the below assertion is commented out, this test will PASS, demonstrating a lifecycle problem.
        $this->assertStringContainsString($expectedFileContents, $pageContent);

        // update the files under test by adding whitespace
        $this->modifySourcePage(
            filePath: $sourcePage,
            regex: '#' . $matchString . '#',
            replacement: '  ' . $matchString . ' ',
            body: $rawPage
        );

        sleep(2);
        $pageContent = file_get_contents($generatedPage);

        // check for the word being tested
        $this->assertStringContainsString($expectedFileContents, $pageContent);

        $process->stop(0);

        // reset the source page
        $this->modifySourcePage(
            filePath: $sourcePage,
            regex: '# *' . $matchString . ' *#',
            replacement: $matchString,
            body: $rawPage
        );
    }

    /** @test */
    public function testComplexLifecycleBuildsProperly_WhileUsingRun(): void
    {
        $sourcePage = __DIR__ . self::PROJECT_DIR . '/source/_clades/1-jacksons.html';
        $generatedPage = __DIR__ . self::PROJECT_DIR . '/' . 'output_test/index.html';
        $expectedFileContents = '<strong>hypothetical</strong>';
        $matchString = "{% include 'jackson.html' %}";

        // ensure consistent test state
        $rawPage = file_get_contents($sourcePage);
        $this->modifySourcePage(
            filePath: $sourcePage,
            regex: '# *' . $matchString . ' *#',
            replacement: $matchString,
            body: $rawPage
        );

        // start our async sculpin watcher/server
        // this may result in TCP port conflicts on some system configurations
        $process = $this->executeSculpinAsync(['run']);

        sleep(1); // wait until our file exists
        $pageContent = file_get_contents($generatedPage);

        // check for the word being tested
        $this->assertStringContainsString($expectedFileContents, $pageContent);

        // update the files under test by adding whitespace
        $this->modifySourcePage(
            filePath: $sourcePage,
            regex: '#' . $matchString . '#',
            replacement: '  ' . $matchString . ' ',
            body: $rawPage
        );

        sleep(2);
        $pageContent = file_get_contents($generatedPage);

        // check for the word being tested
        $this->assertStringContainsString($expectedFileContents, $pageContent);

        $process->stop(0);

        // reset the source page
        $this->modifySourcePage(
            filePath: $sourcePage,
            regex: '# *' . $matchString . ' *#',
            replacement: $matchString,
            body: $rawPage
        );
    }

    protected function modifySourcePage(string $filePath, string $regex, string $replacement, string $body): void
    {
        file_put_contents($filePath, preg_replace($regex, $replacement, $body));
    }
}
