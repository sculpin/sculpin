<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\postsBundle;

use sculpin\event\ConvertSourceEvent;

use sculpin\event\SourceSetEvent;

use sculpin\configuration\Configuration;

use sculpin\Sculpin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PostsBundle extends Bundle implements EventSubscriberInterface {

    /**
     * Configuration key for determining if bundle is enabled
     * @var string
     */
    const CONFIG_ENABLED = 'posts.enabled';
    
    /**
     * Configuration key for directory in which posts are kept
     * @var string
     */
    const CONFIG_DIRECTORY = 'posts.directory';
    
    /**
     * Configuration key for permalink style for posts
     * @var string
     */
    const CONFIG_PERMALINK = 'posts.permalink';

    /**
     * Posts
     * @var Posts
     */
    protected $posts;

    /**
     * The Sculpin object.
     *
     * @var Sculpin
     */
    protected $sculpin;
    
    /**
     * Constructor
     * @param Posts $posts
     */
    public function __construct(Posts $posts = null)
    {
        $this->posts = $posts !== null ? $posts : new Posts();
    }

    /**
     * {@inheritDoc}
     */
    static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_SOURCE_SET_CHANGED => 'sourceSetChanged',
            Sculpin::EVENT_SOURCE_SET_CHANGED_POST => 'sourceSetChangedPost',
            Sculpin::EVENT_AFTER_CONVERT => 'afterConvert',
        );
    }

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        // Extract objects that are required from the container.
        $this->sculpin = $container->get('sculpin');
    }

    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $posts = $this->posts;
        $this->sculpin->registerDataProvider('posts', function(Sculpin $sculpin) use ($posts) { return $posts; });
    }

    /**
     * Called when Sculpin detects source set has changed sources
     * 
     * @param SourceSetEvent $sourceSetEvent
     */
    public function sourceSetChanged(SourceSetEvent $sourceSetEvent)
    {
        $configuration = $sourceSetEvent->configuration();
        if (!$configuration->get(self::CONFIG_ENABLED)) {
            return;
        }

        $pattern = $configuration->get(self::CONFIG_DIRECTORY).'/**';

        foreach ($sourceSetEvent->updatedSources() as $source) {
            /* @var $source \sculpin\source\ISource */
            $relativePathname = $source->relativePathname();
            if ($sourceSetEvent->sculpin()->matcher()->match($pattern, $relativePathname)) {
                if (!$source->data()->get('permalink')) {
                    if ($permalink = $sourceSetEvent->sculpin()->configuration()->get(self::CONFIG_PERMALINK)) {
                        $source->data()->set('permalink', $permalink);
                    }
                }
                if (!$source->data()->get('calculatedDate')) {
                    // we should calculate date from filename
                    if (preg_match('/(\d{4})[\/\-]*(\d{2})[\/\-]*(\d{2})[\/\-]*(\d+?|)/', $source->filename(), $matches)) {
                        list($dummy, $year, $month, $day, $time) = $matches;
                        $parts = array(implode('-', array($year, $month, $day)));
                        if ($time) {
                            $parts[] = $time;
                        }
                        $source->data()->set('calculatedDate', strtotime(implode(' ', $parts)));
                    }
                }
                $this->posts[$source->sourceId()] = $post = new Post($source);
            }
        }
        $this->posts->init();
    }

    /**
     * Called when Sculpin detects source set has changed sources (post)
     * 
     * @param SourceSetEvent $sourceSetEvent
     */
    public function sourceSetChangedPost(SourceSetEvent $sourceSetEvent) {
        $configuration = $sourceSetEvent->configuration();
        if (!$configuration->get(self::CONFIG_ENABLED)) {
            return;
        }
        $aPostHasChanged = false;
        foreach ($this->posts as $post) {
            /* @var $post \sculpin\bundle\postsBundle\Post */
            if ($post->hasChanged()) {
                $aPostHasChanged = true;
                break;
            }
        }
        if ($aPostHasChanged) {
            foreach ($sourceSetEvent->allSources() as $source) {
                /* @var $source \sculpin\source\ISource */
                if ($source->data()->get('use') and in_array('posts', $source->data()->get('use'))) {
                    // Trigger rebuild for anything that uses posts.
                    $source->forceReprocess();
                }
            }
        }
    }

    /**
     * Called when Sculpin detects that source files have been converted
     * 
     * @param ConvertSourceEvent $event
     */
    public function afterConvert(ConvertSourceEvent $convertSourceEvent)
    {
        $configuration = $convertSourceEvent->configuration();
        if (!$configuration->get(self::CONFIG_ENABLED)) {
            return;
        }
        $sourceId = $convertSourceEvent->source()->sourceId();
        if (isset($this->posts[$sourceId])) {
            $post = $this->posts[$sourceId];
            $post->processBlocks($convertSourceEvent->sculpin());
        }
    }

}
