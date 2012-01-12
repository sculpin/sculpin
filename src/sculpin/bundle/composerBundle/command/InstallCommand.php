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

class InstallCommand extends ComposerCommand
{
    
    protected function configure()
    {
        $this
        ->setName('composer:install')
        ->setDescription('Install dependencies. (bundles)')
        ->setHelp(<<<EOT
The <info>composer:install</info> command installs all bundles and dependencies defined in
composer.json in the Sculpin project's root.

You only need to run <info>composer:install</info> once. After that, <info>composer:update</info> should
be run instead. It is capable of installing new and updating existing packages.
EOT
        )
        ;
    }
    
}
