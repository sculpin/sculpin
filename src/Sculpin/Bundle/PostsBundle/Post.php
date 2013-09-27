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

use Sculpin\Contrib\ProxySourceCollection\ProxySourceItem;

/**
 * Post.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Post extends ProxySourceItem
{
    public function date()
    {
        return $this->data()->get('calculated_date');
    }

    public function title()
    {
        return $this->data()->get('title');
    }

    public function previousPost()
    {
        return $this->previousItem();
    }

    public function setPreviousItem(ProxySourceItem $item = null)
    {
        parent::setPreviousItem($item);

        // expose additional metadata
        $this->data()->set('previous_post', $item);
        $this->data()->set('previousPost', $item); // @deprecated
    }

    public function nextPost()
    {
        return $this->nextItem();
    }

    public function setNextItem(ProxySourceItem $item = null)
    {
        parent::setNextItem($item);

        // expose additional metadata
        $this->data()->set('next_post', $item);
        $this->data()->set('nextPost', $item); // @deprecated
    }
}
