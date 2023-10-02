<?php

declare(strict_types=1);
namespace Sculpin\Core\Tests\Source;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use PHPUnit\Framework\TestCase;
use Sculpin\Core\Source\FileSource;
use Symfony\Component\Finder\SplFileInfo;
use Sculpin\Core\Source\DataSourceInterface;

class FileSourceTest extends TestCase
{
    /*
     * mock analyzer for detectFromFilename, should return text/html
     */

    public function makeTestSource($filename, $hasChanged = true)
    {
        $source = new FileSource(
            $this->makeTestDetector(),
            $this->makeTestDatasource(),
            new SplFileInfo($filename, '../Fixtures', $filename),
            false,
            true
        );

        return $source;
    }

    public function makeTestDetector()
    {
        $detector = $this->createMock(FinfoMimeTypeDetector::class);
        $detector
            ->expects($this->any())
            ->method('detectMimeType')
            ->will($this->returnValue('text/yml'));

        return $detector;
    }

    public function makeTestDatasource()
    {
        $datasource = $this->createMock(DataSourceInterface::class);

        $datasource
            ->expects($this->any())
            ->method('dataSourceId')
            ->will($this->returnValue('FilesystemDataSource:test'));

        return $datasource;
    }

    /**
     * @dataProvider provideTestParseYaml
     */
    public function testParseYaml($filename, $msg)
    {
        $expectedOutput = $this->getErrorMessage($filename, $msg);
        ob_end_flush();
        ob_start();
        $this->makeTestSource($filename);
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals($expectedOutput, $output);
    }

    public function provideTestParseYaml()
    {
        return [
            [__DIR__ . '/../Fixtures/valid/no-end-frontmatter.yml', ''],
            [__DIR__ . '/../Fixtures/valid/frontmatter-nocontent.yml', ''],
            [__DIR__ . '/../Fixtures/valid/frontmatter-content.yml', ''],
            [
                __DIR__ . '/../Fixtures/invalid/one-line-edge-case.yml',
                'Yaml could not be parsed, parser detected a string.'
            ],
            [
                __DIR__ . '/../Fixtures/invalid/malformed-yaml.yml',
                'Yaml could not be parsed, parser detected a string.'
            ],
            [
                __DIR__ . '/../Fixtures/invalid/malformed-yaml2.yml',
                'Unable to parse at line 2 (near "first:fsdqf").'
            ],
        ];
    }

    public function getErrorMessage($filename, $msg)
    {
        if ($msg == '') {
            return '';
        }
        return ' ! FileSource:FilesystemDataSource:test:' . $filename . ' ' . $msg . ' !' . PHP_EOL;
    }
}
