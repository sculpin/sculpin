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

namespace Sculpin\Core\Io;

/**
 * IOInterface that is not interactive and never writes the output
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class NullIo implements IoInterface
{
    /**
     * {@inheritDoc}
     */
    public function isInteractive(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function write($messages, bool $newline = true)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function overwrite($messages, bool $newline = true, ?int $size = 80)
    {
    }
}
