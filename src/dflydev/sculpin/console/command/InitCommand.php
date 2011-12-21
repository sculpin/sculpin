<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dflydev\sculpin\console\command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Initialize a project.')
            ->setDefinition(array(
                new InputOption('skeleton', null, InputOption::VALUE_REQUIRED, 'Specify the skeleton (name or path) to use for the initialized site.', 'standard'),
            ))
            ->setHelp(<<<EOT
The <info>init</info> command initializes a project.
EOT
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $skeleton = $input->getOption('skeleton');
    }
}
