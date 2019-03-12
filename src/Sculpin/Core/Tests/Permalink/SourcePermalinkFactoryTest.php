<?php

declare(strict_types=1);

namespace Sculpin\Core\Tests\Permalink;

use Dflydev\DotAccessConfiguration\Configuration;
use PHPUnit\Framework\TestCase;
use Sculpin\Core\Permalink\Permalink;
use Sculpin\Core\Permalink\SourcePermalinkFactory;
use Sculpin\Core\Source\MemorySource;
use Sculpin\Core\Source\SourceInterface;

class SourcePermalinkFactoryTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideCreateData
     * @param string $defaultPermalink
     * @param SourceInterface $source
     * @param Permalink $expectedPermalink
     */
    public function testCreate(string $defaultPermalink, SourceInterface $source, Permalink $expectedPermalink)
    {
        $sourcePermalinkFactory = new SourcePermalinkFactory($defaultPermalink);

        $permalink = $sourcePermalinkFactory->create($source);

        $this->assertEquals($expectedPermalink, $permalink);
    }

    public function provideCreateData()
    {
        return [
            'none setting for permalink' => [
                'none',
                static::makeTestSource('_posts/2015-01-12-from-buttercup-protects-to-broadway.md'),
                new Permalink(
                    '_posts/2015-01-12-from-buttercup-protects-to-broadway.md',
                    '/_posts/2015-01-12-from-buttercup-protects-to-broadway.md'
                ),
            ],

            'pretty permalink page' => [
                'pretty',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about/index.html',
                    '/about'
                ),
            ],

            'basename with html ending' => [
                ':basename.html',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about.html',
                    '/about.html'
                ),
            ],

            'pretty permalink post' => [
                'pretty',
                static::makeTestSource('_posts/2015-01-12-from-buttercup-protects-to-broadway.md'),
                new Permalink(
                    '2015/01/12/from-buttercup-protects-to-broadway/index.html',
                    '/2015/01/12/from-buttercup-protects-to-broadway'
                ),
            ],

            'Permalink with windows path' => [
                ':basename.html',
                static::makeTestSource('some\windows\path.md'),
                new Permalink(
                    'some\windows\path.html',
                    '/some/windows/path.html'
                ),
            ],

            [
                'blog/:year/:month/:day/:slug_title',
                static::makeTestSource('about.md', [
                    'slug' => 'some/about-me',
                    'calculated_date' => mktime(0, 0, 0, 1, 12, 2005)
                ]),
                new Permalink(
                    'blog/2005/01/12/some/about-me/index.html',
                    '/blog/2005/01/12/some/about-me'
                ),
            ],

            [
                ':basename.html/',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about.html/index.html',
                    '/about.html/'
                ),
            ],

            [
                ':filename.html',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about.md.html',
                    '/about.md.html'
                ),
            ],

            [
                ':filename.html/',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about.md.html/index.html',
                    '/about.md.html/'
                ),
            ],

            [
                ':filename',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about.md/index.html',
                    '/about.md'
                ),
            ],

            [
                ':filename/',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about.md/index.html',
                    '/about.md/'
                ),
            ],

            'Permalink for .xml' => [
                ':filename',
                static::makeTestSource('about.xml'),
                new Permalink(
                    'about.xml',
                    '/about.xml'
                ),
            ],

            'Permalink for .json' => [
                ':filename',
                static::makeTestSource('about.json'),
                new Permalink(
                    'about.json',
                    '/about.json'
                ),
            ],

            'Permalink with trailing slash' => [
                ':basename/',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about/index.html',
                    '/about/'
                ),
            ],

            'Folder with basename, no type' => [
                ':folder:basename.html',
                static::makeTestSource('site/about.md'),
                new Permalink(
                    'site/about.html',
                    '/site/about.html'
                ),
            ],

            'Folder with basename, no folder, no type' => [
                ':folder:basename.html',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about.html',
                    '/about.html'
                ),
            ],

            'Folder with basename, with type, no folder' => [
                'posts/:folder:basename.html',
                static::makeTestSource('_posts/somepost.md'),
                new Permalink(
                    'posts/somepost.html',
                    '/posts/somepost.html'
                ),
            ],

            'Folder with basename, with type' => [
                'posts/:folder:basename.html',
                static::makeTestSource('_posts/somefolder/somepost.md'),
                new Permalink(
                    'posts/somefolder/somepost.html',
                    '/posts/somefolder/somepost.html'
                ),
            ],
        ];
    }

    private static function makeTestSource($relativePathname, array $configurationData = [])
    {
        $configuration = new Configuration($configurationData);

        return new MemorySource(
            'testid',
            $configuration,
            '',
            '',
            $relativePathname,
            '',
            new \SplFileInfo('/tmp'),
            false,
            true,
            false
        );
    }
}
