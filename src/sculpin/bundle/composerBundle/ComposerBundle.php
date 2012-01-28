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

use sculpin\bundle\AbstractBundle;
use sculpin\console\Application;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class ComposerBundle extends AbstractBundle{
    
    const CONFIG_EXCLUDE = 'composer.exclude';

    /**
     * (non-PHPdoc)
     * @see sculpin\bundle.AbstractBundle::configureBundle()
     */
    public function configureBundle(Sculpin $sculpin)
    {
        if ($sculpin->sourceIsProjectRoot()) {
            foreach($sculpin->configuration()->get(self::CONFIG_EXCLUDE) as $exclude) {
                $sculpin->exclude($exclude);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see sculpin\bundle.IBundle::CONFIGURE_CONSOLE_APPLICATION()
     */
    static public function CONFIGURE_CONSOLE_APPLICATION(Application $application, InputInterface $input, OutputInterface $output)
    {
        $application->add(new InstallCommand());
        $application->add(new UpdateCommand());
        //$application->add()
    }
    
}
