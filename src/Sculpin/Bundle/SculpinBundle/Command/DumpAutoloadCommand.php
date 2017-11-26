<?php declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\Command;

use Dflydev\EmbeddedComposer\Console\Command\DumpAutoloadCommand as BaseDumpAutoloadCommand;
use Sculpin\Bundle\StandaloneBundle\SculpinStandaloneBundle;

/**
 * Dump Autoload Command.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class DumpAutoloadCommand extends BaseDumpAutoloadCommand
{
    /**
     * {@inheritdoc}
     */
    public function __construct($commandPrefix = 'sculpin:')
    {
        $prefix = class_exists(SculpinStandaloneBundle::class, false)
            ? ''
            : $commandPrefix;

        parent::__construct($prefix);
    }
}
