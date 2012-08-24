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

use dflydev\util\antPathMatcher\AntPathMatcher;
use Sculpin\Core\DataProvider\DataProviderInterface;
use Sculpin\Core\Event\ConvertEvent;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Sculpin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Posts Data Provider.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class PostsDataProvider implements DataProviderInterface, EventSubscriberInterface
{
    /**
     * Sculpin
     *
     * @var Sculpin
     */
    protected $sculpin;

    /**
     * Paths
     *
     * @var array
     */
    protected $paths;

    /**
     * Default permalink
     *
     * @var string
     */
    protected $defaultPermalink;

    /**
     * Matcher
     *
     * @var AntPathMatcher
     */
    protected $matcher;

    /**
     * Posts
     *
     * @var Posts
     */
    protected $posts;

    /**
     * Constructor
     *
     * @param Sculpin        $sculpin          Sculpin
     * @param array          $paths            Paths
     * @param string         $defaultPermalink Default permalink
     * @param AntPathMatcher $matcher          Matcher
     * @param Posts          $posts            Posts
     */
    public function __construct(Sculpin $sculpin, array $paths, $defaultPermalink = null, AntPathMatcher $matcher = null, Posts $posts = null)
    {
        $this->sculpin = $sculpin;
        $this->paths = $paths;
        $this->defaultPermalink = $defaultPermalink;
        $this->matcher = $matcher ?: new AntPathMatcher;
        $this->posts = $posts ?: new Posts;
    }

    /**
     * {@inheritdoc}
     */
    public function provideData()
    {
        return $this->posts;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Sculpin::EVENT_BEFORE_RUN => 'beforeRun',
            Sculpin::EVENT_BEFORE_RUN_AGAIN => 'beforeRunAgain',
            Sculpin::EVENT_AFTER_CONVERT => 'afterConvert',
        );
    }

    /**
     * Before run
     *
     * @param SourceSetEvent $sourceSetEvent Source Set Event
     */
    public function beforeRun(SourceSetEvent $sourceSetEvent)
    {
        foreach ($this->paths as $path) {
            $pattern = $this->matcher->isPattern($path) ? $path : $path.'/**';
            foreach ($sourceSetEvent->updatedSources() as $source) {
                if ($this->matcher->match($pattern, $source->relativePathname())) {
                    if (!$source->data()->get('permalink') and $this->defaultPermalink) {
                        $source->data()->set('permalink', $this->defaultPermalink);
                    }
                    if (!$source->data()->get('calculatedDate')) {
                        if (preg_match('/(\d{4})[\/\-]*(\d{2})[\/\-]*(\d{2})[\/\-]*(\d+?|)/', $source->filename(), $matches)) {
                            list($dummy, $year, $month, $day, $time) = $matches;
                            $parts = array(implode('-', array($year, $month, $day)));
                            if ($time) {
                                $parts[] = $time;
                            }
                            $source->data()->set('calculatedDate', $calculatedDate = strtotime(implode(' ', $parts)));
                            if (!$source->data()->get('date')) {
                                $source->data()->set('date', date('c', $calculatedDate));
                            }
                        }
                    }
                    $this->posts[$source->sourceId()] = new Post($source);
                }
            }
        }
        $this->posts->init();
    }

    /**
     * Before run (again)
     *
     * @param SourceSetEvent $sourceSetEvent Source Set Event
     */
    public function beforeRunAgain(SourceSetEvent $sourceSetEvent)
    {
        $aPostHasChanged = false;
        foreach ($this->posts as $post) {
            if ($post->hasChanged()) {
                $aPostHasChanged = true;
                $this->posts->init();
                break;
            }
        }
        if ($aPostHasChanged) {
            foreach ($sourceSetEvent->allSources() as $source) {
                if ($source->data()->get('use') and in_array('posts', $source->data()->get('use'))) {
                    $source->forceReprocess();
                }
            }
        }
    }

    /**
     * Called after conversion
     *
     * @param ConvertEvent $convertEvent Convert event
     */
    public function afterConvert(ConvertEvent $convertEvent)
    {
        $sourceId = $convertEvent->source()->sourceId();
        if (isset($this->posts[$sourceId])) {
            $post = $this->posts[$sourceId];
            $post->setBlocks($this->sculpin->formatSourceBlocks($convertEvent->source()));
        }
    }
}
