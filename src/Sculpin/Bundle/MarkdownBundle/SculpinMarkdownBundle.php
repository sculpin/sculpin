<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\MarkdownBundle;

use Sculpin\Core\Bundle\AbstractBundle;
use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Sculpin;

/**
 * Sculpin Markdown Bundle.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinMarkdownBundle extends AbstractBundle
{
    const CONVERTER_NAME = 'markdown';
}
