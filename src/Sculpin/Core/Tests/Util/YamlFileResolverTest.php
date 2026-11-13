<?php

declare(strict_types=1);
namespace Sculpin\Core\Tests\Util;

use PHPUnit\Framework\TestCase;
use Sculpin\Core\Util\YamlFileResolver;

final class YamlFileResolverTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/sculpin-yaml-file-resolver-'.uniqid();

        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        if (file_exists($path = $this->tempDir.'/both.yml')) {
            unlink($path);
        }

        if (file_exists($path = $this->tempDir.'/both.yaml')) {
            unlink($path);
        }

        if (file_exists($path = $this->tempDir.'/yaml-only.yaml')) {
            unlink($path);
        }

        rmdir($this->tempDir);
    }

    public function testPrefersYmlOverYaml(): void
    {
        touch($this->tempDir.'/both.yml', time() - 1);
        touch($this->tempDir.'/both.yaml');

        $this->assertSame(
            $this->tempDir.'/both.yml',
            YamlFileResolver::resolve($this->tempDir.'/both')
        );
    }

    public function testResolvesYamlWhenYmlIsMissing(): void
    {
        touch($this->tempDir.'/yaml-only.yaml');

        $this->assertSame(
            $this->tempDir.'/yaml-only.yaml',
            YamlFileResolver::resolve($this->tempDir.'/yaml-only')
        );
    }

    public function testReturnsNullWhenNeitherFileExists(): void
    {
        $this->assertNull(YamlFileResolver::resolve($this->tempDir.'/missing'));
    }
}
