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

use sculpin\Sculpin;

use sculpin\bundle\composerBundle\command\InstallCommand;
use sculpin\bundle\composerBundle\command\UpdateCommand;

use sculpin\console\Application;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ComposerBundle extends Bundle {
    
    const CONFIG_EXCLUDE = 'composer.exclude';

    /**
     * @{inheritdoc}
     */
    public function boot()
    {
        $sculpin = $this->container->get('sculpin');
        if ($sculpin->sourceDirIsProjectDir()) {
            foreach($sculpin->get('sculpin.configuration')->get(self::CONFIG_EXCLUDE) as $exclude) {
                $sculpin->addExclude($exclude);
            }
        }
    }
}
