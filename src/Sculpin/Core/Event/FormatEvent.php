<?php declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Event;

use Sculpin\Core\Formatter\FormatContext;

/**
 * Format Event.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FormatEvent extends Event
{
    /**
     * Format Context
     *
     * @var FormatContext
     */
    protected $formatContext;

    /**
     * Constructor.
     *
     * @param FormatContext $formatContext Format context
     */
    public function __construct(FormatContext $formatContext)
    {
        $this->formatContext = $formatContext;
    }

    /**
     * Format Context
     */
    public function formatContext(): FormatContext
    {
        return $this->formatContext;
    }
}
