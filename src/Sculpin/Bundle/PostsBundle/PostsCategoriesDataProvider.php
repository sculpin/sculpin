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
 * Posts Categories Data Provider.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class PostsCategoriesDataProvider implements DataProviderInterface, EventSubscriberInterface
{
    protected $postCategories = array();

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
        return $this->postCategories;
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
        $postCategories = array();
        foreach ($this->dataProviderManager->dataProvider('posts')->provideData() as $post) {
            if ($categories = $post->data()->get('categories')) {
                $normalizedCategories = array();
                foreach ((array) $categories as $category) {
                    $normalizedCategory = trim($category);
                    $postCategories[$normalizedCategory][] = $post;
                    $normalizedCategories[] = $normalizedCategory;
                }
                $post->data()->set('categories', $normalizedCategories);
            }
        }

        $this->postCategories = $postCategories;
    }
}
