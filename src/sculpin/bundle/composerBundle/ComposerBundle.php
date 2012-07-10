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
     * The Sculpin object.
     *
     * @var Sculpin
     */
    protected $sculpin;
    protected $configuration;

    /**
     * @{inheritDoc}
     */
    public function build(ContainerBuilder $container) {
        // Extract objects that are required from the container.
        $this->configuration = $container->get('sculpin.configuration');
        $this->sculpin = $container->get('sculpin');
    }

    /**
     * @{inheritdoc}
     */
    public function boot()
    {
        if ($this->sculpin->sourceDirIsProjectDir()) {
            foreach($this->configuration->get(self::CONFIG_EXCLUDE) as $exclude) {
                $this->sculpin->addExclude($exclude);
            }
        }
    }
}
