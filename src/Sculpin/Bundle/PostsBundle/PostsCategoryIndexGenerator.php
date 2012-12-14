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
 * Posts Category Index Generator.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class PostsCategoryIndexGenerator implements GeneratorInterface
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
        $categories = $this->dataProviderManager->dataProvider('posts_categories')->provideData();

        $generatedSources = array();
        foreach ($categories as $category => $posts) {
            $generatedSource = $source->duplicate(
                $source->sourceId().':category='.$category
            );

            $permalink = $source->data()->get('permalink') ?: $source->relativePathname();
            $basename = basename($permalink);

            $permalink = dirname($permalink);

            if (preg_match('/^(.+?)\.(.+)$/', $basename, $matches)) {
                $permalink = $permalink.'/'.$category.'/index.html';
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

            $generatedSource->data()->set('categoried_posts', $posts);
            $generatedSource->data()->set('category', $category);

            $generatedSources[] = $generatedSource;
        }

        return $generatedSources;
    }
}
