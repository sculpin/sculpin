<?php

declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Permalink;

use Sculpin\Core\Source\SourceInterface;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
interface SourcePermalinkFactoryInterface
{
    /**
     * Create a Permalink for a Source.
     */
    public function create(SourceInterface $source): PermalinkInterface;
}
