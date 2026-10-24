<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional;

use Sculpin\Bundle\SculpinBundle\Command\InitCommand;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

final class InitCommandTest extends FunctionalTestCase
{
    protected const PROJECT_DIR = '/__BlankSculpinProject__';

    private Finder $finder;

    #[\Override]
    protected function setUp(): void
    {
        $this->tearDownTestProject();
        $this->addProjectDirectory('', $recursive = false);
        $this->finder = new Finder();
    }

    /** @test */
    public function shouldInitSpecifiedOutputDir(): void
    {
        $projectDir = self::projectDir();
        $this->assertProjectEmpty($projectDir);

        $this->executeSculpin(['init', '--no-posts', '-t', InitCommand::DEFAULT_TITLE]);

        $this->assertProjectInitialized($projectDir);

        $this->assertYamlFileEqualsArray(
            ['sculpin_content_types' => ['posts' => ['enabled' => false]]],
            $projectDir . '/app/config/sculpin_kernel.yml'
        );

        $this->assertYamlFileEqualsArray(
            [
                'title'                        => InitCommand::DEFAULT_TITLE,
                'subtitle'                     => InitCommand::DEFAULT_SUBTITLE,
                'google_analytics_tracking_id' => '',
                'url'                          => '',
            ],
            $projectDir . '/app/config/sculpin_site.yml'
        );
    }

    /** @test */
    public function shouldFailWhenAppOrSourceFoldersExist(): void
    {
        $projectDir = self::projectDir();
        $this->assertProjectEmpty($projectDir);

        // The check currently only looks for "/app" and "/source",
        // so the folder could contain other content (e.g., if someone has
        // an existing site structure they want to augment with Sculpin)
        file_put_contents($projectDir . '/source', 'fail this test');

        $this->assertProjectNotEmpty($projectDir);

        $process = $this->executeSculpinAsync(
            ['init', '-t', 'My Custom Title', '-s', 'Custom Subtitle', '--no-posts'],
            start: false,
        );
        $result = $process->run();

        $this->executeOutput = $process->getOutput();
        $this->errorOutput = $process->getErrorOutput();

        self::assertSame(101, $result);
        self::assertStringContainsString(
            "/source folder exists.\nThis command can only be run in an uninitialized folder.",
            $this->executeOutput
        );
    }

    /** @test */
    public function shouldInitWithSpecifiedParameters_NoPosts(): void
    {
        $projectDir = self::projectDir();
        $this->assertProjectEmpty($projectDir);

        $this->executeSculpin(['init', '-t', 'My Custom Title', '-s', 'Custom Subtitle', '--no-posts']);

        $this->assertProjectInitialized($projectDir);

        $this->assertYamlFileEqualsArray(
            ['sculpin_content_types' => ['posts' => ['enabled' => false]]],
            $projectDir . '/app/config/sculpin_kernel.yml'
        );

        $this->assertYamlFileEqualsArray(
            [
                'title'                        => 'My Custom Title',
                'subtitle'                     => 'Custom Subtitle',
                'google_analytics_tracking_id' => '',
                'url'                          => '',
            ],
            $projectDir . '/app/config/sculpin_site.yml'
        );
    }

    /** @test */
    public function shouldInitWithSpecifiedParameters_PostsEnabled(): void
    {
        $projectDir = self::projectDir();
        $this->assertProjectEmpty($projectDir);

        $this->executeSculpin(['init', '-t', 'My Custom Title', '-s', 'Custom Subtitle', '-p']);

        $this->assertProjectInitialized(
            $projectDir,
            [
                '/source/_posts',
                '/source/_posts/first_post.md',
                '/source/_views/post.html',
                '/source/posts',
                '/source/posts.html',
                '/source/posts/categories',
                '/source/posts/categories.html',
                '/source/posts/categories/category.html',
                '/source/posts/tags',
                '/source/posts/tags.html',
                '/source/posts/tags/tag.html',
            ]
        );

        $this->assertYamlFileEqualsArray(
            [
                'sculpin_content_types' => [
                    'posts' => [
                        'type' => 'path',
                        'path' => '_posts',
                        'permalink' => 'pretty',
                        'taxonomies' => [
                            'tags',
                            'categories',
                        ]
                    ]
                ]
            ],
            $projectDir . '/app/config/sculpin_kernel.yml'
        );

        $this->assertYamlFileEqualsArray(
            [
                'title'                        => 'My Custom Title',
                'subtitle'                     => 'Custom Subtitle',
                'google_analytics_tracking_id' => '',
                'url'                          => '',
            ],
            $projectDir . '/app/config/sculpin_site.yml'
        );
    }

    private function assertProjectEmpty(string $projectDir): void
    {
        $files = $this->finder->in($projectDir);
        $this->assertSame(
            [],
            array_keys(iterator_to_array($files)),
            'Expected project dir to be empty'
        );
    }

    private function assertProjectNotEmpty(string $projectDir): void
    {
        $files = $this->finder->in($projectDir);
        $this->assertGreaterThan(
            0,
            count(array_keys(iterator_to_array($files))),
            'Expected project dir to be empty'
        );
    }

    private function assertProjectInitialized(string $projectDir, array $extraExpected = []): void
    {
        $files = $this->finder->in($projectDir);

        $expected = array_merge([
            $projectDir . '/app',
            $projectDir . '/app/config',
            $projectDir . '/app/config/sculpin_site.yml',
            $projectDir . '/app/config/sculpin_kernel.yml',
            $projectDir . '/app/SculpinKernel.php',
            $projectDir . '/source',
            $projectDir . '/source/_includes',
            $projectDir . '/source/_includes/macros.twig',
            $projectDir . '/source/_views',
            $projectDir . '/source/_views/default.html',
            $projectDir . '/source/index.md',
        ], array_map(fn ($file) => $projectDir . $file, $extraExpected));

        $actual = array_keys(iterator_to_array($files));

        sort($expected);
        sort($actual);

        $this->assertSame($expected, $actual);
    }

    private function assertYamlFileEqualsArray(array $expected, string $file): void
    {
        $this->assertSame($expected, Yaml::parseFile($file));
    }
}
