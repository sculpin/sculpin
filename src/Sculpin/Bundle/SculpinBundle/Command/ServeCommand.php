<?php

declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\Command;

use Sculpin\Bundle\SculpinBundle\HttpServer\HttpServer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class ServeCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $prefix = $this->isStandaloneSculpin() ? '' : 'sculpin:';

        $this
            ->setName($prefix.'serve')
            ->setDescription('Serve a site.')
            ->setDefinition([
                new InputOption('port', null, InputOption::VALUE_REQUIRED, 'Port'),
            ])
            ->setHelp(<<<EOT
The <info>serve</info> command serves a site.

EOT
            )->setAliases(['server']);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $docroot = $this->getContainer()->getParameter('sculpin.output_dir');
        $kernel = $this->getContainer()->get('kernel');

        $httpServer = new HttpServer(
            $output,
            $docroot,
            $kernel->getEnvironment(),
            $kernel->isDebug(),
            (int) $input->getOption('port')
        );

        $httpServer->run();

        return 0;
    }
}
