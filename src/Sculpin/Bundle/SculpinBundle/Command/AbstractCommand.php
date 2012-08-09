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

use Sculpin\Core\Console\Command\ContainerAwareCommand;

/**
 * Generate Command.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
abstract class AbstractCommand extends ContainerAwareCommand
{
    /**
     * Test to see if Sculpin is running in standalone mode.
     *
     * @return bool
     */
    protected function isStandaloneSculpin()
    {
        return class_exists('Sculpin\\Bundle\\StandaloneBundle\\SculpinStandaloneBundle', false);
    }
}
