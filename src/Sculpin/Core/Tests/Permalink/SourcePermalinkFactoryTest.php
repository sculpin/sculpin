<?php declare(strict_types=1);

namespace Sculpin\Core\Tests\Permalink;

use Dflydev\DotAccessConfiguration\Configuration;
use Sculpin\Core\Permalink\Permalink;
use Sculpin\Core\Permalink\SourcePermalinkFactory;
use Sculpin\Core\Source\MemorySource;
use Sculpin\Core\Source\SourceInterface;

class SourcePermalinkFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @dataProvider provideCreateData
     * @param string $defaultPermalink
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
        ];
    }

    private static function makeTestSource($relativePathname, array $configuration = [])
    {
        $configuration = new Configuration($configuration);

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
