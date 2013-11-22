<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\Command;

use Dflydev\EmbeddedComposer\Console\Command\UpdateCommand as BaseUpdateCommand;

/**
 * Update Command.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class UpdateCommand extends BaseUpdateCommand
{
    /**
     * {@inheritdoc}
     */
    public function __construct($commandPrefix = 'sculpin:')
    {
        $prefix = class_exists('Sculpin\\Bundle\\StandaloneBundle\\SculpinStandaloneBundle', false)
            ? ''
            : $commandPrefix;

        parent::__construct($prefix);
    }
}
