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

namespace Sculpin\Bundle\SculpinBundle\Command;

use Sculpin\Bundle\StandaloneBundle\SculpinStandaloneBundle;
use Sculpin\Core\Console\Command\ContainerAwareCommand;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
abstract class AbstractCommand extends ContainerAwareCommand
{
    /**
     * Test to see if Sculpin is running in standalone mode.
     */
    protected function isStandaloneSculpin(): bool
    {
        return class_exists(SculpinStandaloneBundle::class, false);
    }
}
