<?php

/*
 * This file is a part of Sculpin
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\composerBundle;

use sculpin\bundle\AbstractBundle;
use sculpin\bundle\composerBundle\command\InstallCommand;
use sculpin\bundle\composerBundle\command\UpdateCommand;
use sculpin\console\Application;
use sculpin\Sculpin;

/**
 * Composer Bundle
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class ComposerBundle extends AbstractBundle
{
    const CONFIG_EXCLUDE = 'composer.exclude';

    /**
     * @{inheritdoc}
     */
    public function boot()
    {
        if ($this->sculpin->sourceDirIsProjectDir()) {
            foreach ($this->configuration->get(self::CONFIG_EXCLUDE) as $exclude) {
                $this->sculpin->addExclude($exclude);
            }
        }
    }
}
