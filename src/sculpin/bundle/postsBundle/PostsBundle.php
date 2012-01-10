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

use sculpin\event\SourceFilesChangedEvent;

use sculpin\Sculpin;

use sculpin\bundle\AbstractBundle;

class PostsBundle extends AbstractBundle {

    /**
     * Configuration key for determining if bundle is enabled
     * @var unknown_type
     */
    const CONFIG_ENABLED = 'posts.enabled';
    
    /**
     * Configuration key for directory in which posts are kept
     * @var unknown_type
     */
    const CONFIG_DIRECTORY = 'posts.directory';
    
    /**
     * Posts
     * @var Post[]
     */
    protected $posts = array();

    /**
     * (non-PHPdoc)
     * @see sculpin\bundle.AbstractBundle::getBundleEvents()
     */
    static function getBundleEvents()
    {
        return array(
            Sculpin::EVENT_SOURCE_FILES_CHANGED => 'sourceFilesChanged',
            Sculpin::EVENT_PROCESSED => 'processed',
        );
    }

    /**
     * Called when Sculpin detects any source files have changed
     * @param SourceFilesChangedEvent $event
     */
    public function sourceFilesChanged(SourceFilesChangedEvent $event)
    {
        if (!$this->isEnabled($event, self::CONFIG_ENABLED)) { return; }
        $configuration = $event->configuration();
        $pattern = $configuration->get(self::CONFIG_DIRECTORY).'/**';
        foreach ($event->inputFiles()->allFiles() as $inputFile) {
            /* @var $inputFile \sculpin\source\SourceFile */
            if ($event->sculpin()->matcher()->match($pattern, $inputFile->file()->getRelativePathname())) {
                $this->posts[$inputFile->id()] = $post = new Post($inputFile);
            }
        }
    }

    public function processed(SourceFilesChangedEvent $event)
    {
        if (!$this->isEnabled($event, self::CONFIG_ENABLED)) { return; }
        foreach ($this->posts as $post) {
            $post->processBlocks($event->sculpin());
        }
    }

}
