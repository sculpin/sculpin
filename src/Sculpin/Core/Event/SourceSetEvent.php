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

namespace Sculpin\Core\Event;

use Sculpin\Core\Source\SourceInterface;
use Sculpin\Core\Source\SourceSet;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class SourceSetEvent extends Event
{
    /**
     * @var SourceSet
     */
    private $sourceSet;

    public function __construct(SourceSet $sourceSet)
    {
        $this->sourceSet = $sourceSet;
    }

    /**
     * @return SourceInterface[]
     */
    public function allSources(): array
    {
        return $this->sourceSet->allSources();
    }

    /**
     * @return SourceInterface[]
     */
    public function updatedSources(): array
    {
        return $this->sourceSet->updatedSources();
    }

    public function sourceSet(): SourceSet
    {
        return $this->sourceSet;
    }
}
