<?php

declare(strict_types=1);
namespace Sculpin\Core\Tests\Source;

use PHPUnit\Framework\MockObject\MockObject;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use PHPUnit\Framework\TestCase;
use Sculpin\Core\Source\FileSource;
use Symfony\Component\Finder\SplFileInfo;
use Sculpin\Core\Source\DataSourceInterface;

final class FileSourceTest extends TestCase
{
    /*
     * mock analyzer for detectFromFilename, should return text/html
     */

    public function makeTestSource($filename, $hasChanged = true): FileSource
    {
        return new FileSource(
            $this->makeTestDetector(),
            $this->makeTestDatasource(),
            new SplFileInfo($filename, '../Fixtures', $filename),
            false,
            true
        );
    }

    public function makeTestDetector(): MockObject
    {
        $detector = $this->createMock(FinfoMimeTypeDetector::class);
        $detector
            ->expects($this->any())
            ->method('detectMimeType')
            ->will($this->returnValue('text/yml'));

        return $detector;
    }

    public function makeTestDatasource(): MockObject
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
    public function testParseYaml(string $filename, string $msg): void
    {
        $expectedOutput = $this->getErrorMessage($filename, $msg);
        ob_end_flush();
        ob_start();
        $this->makeTestSource($filename);
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals($expectedOutput, $output);
    }

    public function provideTestParseYaml(): array
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

    public function getErrorMessage(string $filename, ?string $msg): string
    {
        if ($msg == '') {
            return '';
        }

        return ' ! FileSource:FilesystemDataSource:test:' . $filename . ' ' . $msg . ' !' . PHP_EOL;
    }
}
