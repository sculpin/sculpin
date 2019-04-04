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

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
interface PermalinkInterface
{
    public function relativeFilePath(): string;

    public function relativeUrlPath(): string;
}
