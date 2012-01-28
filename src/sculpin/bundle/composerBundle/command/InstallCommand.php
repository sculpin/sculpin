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

use Composer\DependencyResolver\Request;


use Composer\Package\LinkConstraint\VersionConstraint;

use Composer\Json\JsonFile;

use Composer\Repository\FilesystemRepository;

use Composer\Command\InstallCommand as BaseInstallCommand;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends BaseInstallCommand
{
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Console\Command.Command::configure()
     */
    protected function configure()
    {
        parent::configure();
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composer = Factory::create(
            new ConsoleIO(
                $input, $output, $this->getApplication()->getHelperSet()
            )
        );
        $filesystemRepository = new FilesystemRepository(
            new JsonFile('/home/altern8/k/vendor/.composer/installed.json')
        );
        return $this->install(
            $composer,
            $input,
            $output,
            false,
            (Boolean)$input->getOption('dev'),
            (Boolean)$input->getOption('dry-run'),
            (Boolean)$input->getOption('verbose'),
            (Boolean)$input->getOption('no-install-recommends'),
            (Boolean)$input->getOption('install-suggests'),
            $filesystemRepository
        );
    }
}
