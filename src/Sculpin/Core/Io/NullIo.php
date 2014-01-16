<?php

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
    public function isInteractive()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function write($messages, $newline = true)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function overwrite($messages, $newline = true, $size = 80)
    {
    }
}
