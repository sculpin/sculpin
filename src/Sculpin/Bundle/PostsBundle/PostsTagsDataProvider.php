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

use Sculpin\Core\DataProvider\DataProviderInterface;
use Sculpin\Core\DataProvider\DataProviderManager;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Sculpin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Posts Tags Data Provider.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class PostsTagsDataProvider implements DataProviderInterface, EventSubscriberInterface
{
    protected $postTags = array();

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
    public function provideData()
    {
        return $this->postTags;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_BEFORE_RUN_AGAIN => 'beforeRun',
        );
    }

    /**
     * Before run (again)
     *
     * @param SourceSetEvent $sourceSetEvent Source Set Event
     */
    public function beforeRun(SourceSetEvent $sourceSetEvent)
    {
        $postTags = array();
        foreach ($this->dataProviderManager->dataProvider('posts')->provideData() as $post) {
            if ($tags = $post->data()->get('tags')) {
                $normalizedTags = array();
                foreach ((array) $tags as $tag) {
                    $normalizedTag = trim($tag);
                    $postTags[$normalizedTag][] = $post;
                    $normalizedTags[] = $normalizedTag;
                }
                $post->data()->set('tags', $normalizedTags);
            }
        }

        $this->postTags = $postTags;
    }
}
