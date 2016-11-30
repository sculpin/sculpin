<?php

namespace Sculpin\Core\Tests\Permalink;

use Dflydev\DotAccessConfiguration\Configuration;
use Sculpin\Core\Permalink\Permalink;
use Sculpin\Core\Permalink\SourcePermalinkFactory;
use Sculpin\Core\Source\MemorySource;
use Sculpin\Core\Source\SourceInterface;

class SourcePermalinkFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider provideCreateData
     * @param string $defaultPermalink
     * @param SourceInterface $source
     * @param Permalink $expectedPermalink
     */
    public function testCreate($defaultPermalink, SourceInterface $source, Permalink $expectedPermalink)
    {
        $sourcePermalinkFactory = new SourcePermalinkFactory($defaultPermalink);

        $permalink = $sourcePermalinkFactory->create($source);

        $this->assertEquals($expectedPermalink, $permalink);
    }

    public function provideCreateData()
    {
        return array(
            'none setting for permalink' => array(
                'none',
                static::makeTestSource('_posts/2015-01-12-from-buttercup-protects-to-broadway.md'),
                new Permalink(
                    '_posts/2015-01-12-from-buttercup-protects-to-broadway.md',
                    '/_posts/2015-01-12-from-buttercup-protects-to-broadway.md'
                ),
            ),

            'pretty permalink page' => array(
                'pretty',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about/index.html',
                    '/about'
                ),
            ),

            'basename with html ending' => array(
                ':basename.html',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about.html',
                    '/about.html'
                ),
            ),

            'pretty permalink post' => array(
                'pretty',
                static::makeTestSource('_posts/2015-01-12-from-buttercup-protects-to-broadway.md'),
                new Permalink(
                    '2015/01/12/from-buttercup-protects-to-broadway/index.html',
                    '/2015/01/12/from-buttercup-protects-to-broadway'
                ),
            ),

            'Permalink with trailing slash' => array(
                ':basename/',
                static::makeTestSource('about.md'),
                new Permalink(
                    'about/index.html',
                    '/about/'
                ),
            ),
        );
    }

    private static function makeTestSource($relativePathname, array $configuration = array())
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
