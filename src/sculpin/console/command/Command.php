<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\console\command;

use Symfony\Component\Console\Command\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    /**
     * Get the Sculpin application
     * @return \sculpin\console\Application
     */
    public function getSculpinApplication()
    {
        return $this->getApplication();
    }
}
