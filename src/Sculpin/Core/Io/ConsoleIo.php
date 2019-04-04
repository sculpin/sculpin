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

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console I/O implementation.
 *
 * @author François Pluchino <francois.pluchino@opendisplay.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
final class ConsoleIo implements IoInterface
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * The last message that has been output, to be able to overwrite it.
     *
     * @var string
     */
    private $lastMessage;

    /**
     * Time in seconds with fractions when debugging has been enabled.
     *
     * @var float
     */
    private $startTime;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function enableDebugging(float $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * {@inheritDoc}
     */
    public function isInteractive(): bool
    {
        return $this->input->isInteractive();
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated(): bool
    {
        return $this->output->isDecorated();
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose(): bool
    {
        return $this->output->isVerbose();
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose(): bool
    {
        return $this->output->isVeryVerbose();
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug(): bool
    {
        return $this->output->isDebug();
    }

    /**
     * {@inheritDoc}
     */
    public function write($messages, bool $newline = true)
    {
        if (null !== $this->startTime) {
            $messages = (array) $messages;
            $messages[0] = sprintf(
                '[%.1fMB/%.2fs] %s',
                memory_get_usage() / 1024 / 1024,
                microtime(true) - $this->startTime,
                $messages[0]
            );
        }
        $this->output->write($messages, $newline);
        $this->lastMessage = join($newline ? "\n" : '', (array) $messages);
    }

    /**
     * {@inheritDoc}
     */
    public function overwrite($messages, bool $newline = true, ?int $size = null)
    {
        // messages can be an array, let's convert it to string anyway
        $messages = join($newline ? "\n" : '', (array) $messages);

        // since overwrite is supposed to overwrite last message...
        if (!isset($size)) {
            // removing possible formatting of lastMessage with strip_tags
            $size = strlen(strip_tags($this->lastMessage));
        }
        // ...let's fill its length with backspaces
        $this->write(str_repeat("\x08", $size), false);

        // write the new message
        $this->write($messages, false);

        $fill = $size - strlen(strip_tags($messages));
        if ($fill > 0) {
            // whitespace whatever has left
            $this->write(str_repeat(' ', $fill), false);
            // move the cursor back
            $this->write(str_repeat("\x08", $fill), false);
        }

        if ($newline) {
            $this->write('');
        }
        $this->lastMessage = $messages;
    }
}
