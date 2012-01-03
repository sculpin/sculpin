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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Generate a site from source.')
            ->setDefinition(array(
                new InputOption('watch', null, InputOption::VALUE_NONE, 'Watch source and regenerate site as changes are made.'),
                //new InputOption('server', null, InputOption::VALUE_NONE, 'Serve generated site.'),
                //new InputOption('port', null, InputOption::VALUE_REQUIRED, 'Specify the port that the server should listen on.', 5000),
                new InputOption('url', null, InputOption::VALUE_REQUIRED, 'Override site.url configuration.'),
            ))
            ->setHelp(<<<EOT
The <info>generate</info> command generates a site from source.

Sculpin can also watch the source for changes and regenerate parts of the site
as changes are made to the source. This can be useful during development.
EOT
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $watch = (Boolean) $input->getOption('watch');
        //$server = (Boolean) $input->getOption('server');
        //$port = $input->getOption('port');
        $url = $input->getOption('url');
        $sculpin = $this->getSculpinApplication()->createSculpin();
        $sculpin->start();
        $sculpin->run($watch);
        $sculpin->stop();
    }
}
