<?php

/*
 * This file is a part of Sculpin
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\composerBundle\command;

use Symfony\Component\Console\Input\InputOption;

use sculpin\bundle\composerBundle\command\ComposerCommand;

class UpdateCommand extends ComposerCommand
{
    
    protected function configure()
    {
        $this
        ->setName('composer:update')
        ->setDescription('Updates dependencies. (bundles)')
        ->setHelp(<<<EOT
The <info>composer:update</info> command reads dependencies defined in composer.json in
the Sculpin project's root and installs and updates dependencies for the project.
EOT
        )
        ;
    }
    
}
