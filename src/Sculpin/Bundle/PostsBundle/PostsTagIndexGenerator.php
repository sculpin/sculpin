<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\PostsBundle;

use Sculpin\Core\DataProvider\DataProviderManager;
use Sculpin\Core\Generator\GeneratorInterface;
use Sculpin\Core\Source\SourceInterface;

/**
 * Posts Tag Index Generator.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class PostsTagIndexGenerator implements GeneratorInterface
{
    /**
     * Data Provider Manager
     *
     * @var DataProviderManager
     */
    protected $dataProviderManager;

    /**
     * Constructor
     *
     * @param DataProviderManager $dataProviderManager Data Provider Manager
     */
    public function __construct(DataProviderManager $dataProviderManager)
    {
        $this->dataProviderManager = $dataProviderManager;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SourceInterface $source)
    {
        $generatedSources = array();
        $tags = $this->dataProviderManager->dataProvider('posts_tags')->provideData();

        $generatedSources = array();
        foreach ($tags as $tag => $posts) {
            $generatedSource = $source->duplicate(
                $source->sourceId().':tag='.$tag
            );

            $permalink = $source->data()->get('permalink') ?: $source->relativePathname();
            $basename = basename($permalink);

            $permalink = dirname($permalink);

            if (preg_match('/^(.+?)\.(.+)$/', $basename, $matches)) {
                $permalink = $permalink.'/'.$tag.'/index.html';
            } else {
                // not sure what case this is?
            }

            if (0 === strpos($permalink, './')) {
                $permalink = substr($permalink, 2);
            }

            if ($permalink) {
                // not sure if this is ever going to happen?
                $generatedSource->data()->set('permalink', $permalink);
            }

            $generatedSource->data()->set('tagged_posts', $posts);
            $generatedSource->data()->set('tag', $tag);

            $generatedSources[] = $generatedSource;
        }

        return $generatedSources;
    }
}
