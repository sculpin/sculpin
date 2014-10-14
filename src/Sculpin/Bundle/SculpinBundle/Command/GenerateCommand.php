<?php

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
use Sculpin\Core\Io\ConsoleIo;
use Sculpin\Core\Source\SourceSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate Command.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class GenerateCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $prefix = $this->isStandaloneSculpin() ? '' : 'sculpin:';

        $this
            ->setName($prefix.'generate')
            ->setDescription('Generate a site from source.')
            ->setDefinition(array(
                new InputOption('watch', null, InputOption::VALUE_NONE, 'Watch source and regenerate site as changes are made.'),
                new InputOption('server', null, InputOption::VALUE_NONE, 'Start an HTTP server to host your generated site'),
                new InputOption('url', null, InputOption::VALUE_REQUIRED, 'Override URL.'),
                new InputOption('port', null, InputOption::VALUE_REQUIRED, 'Port'),
            ))
            ->setHelp(<<<EOT
The <info>generate</info> command generates a site.

EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getApplication()->getMissingSculpinBundlesMessages() as $message) {
            $output->writeln($message);
        }

        $watch = $input->getOption('watch') ?: false;
        $sculpin = $this->getContainer()->get('sculpin');
        $dataSource = $this->getContainer()->get('sculpin.data_source');
        $sourceSet = new SourceSet;

        $config = $this->getContainer()->get('sculpin.site_configuration');
        if ($url = $input->getOption('url')) {
            $config->set('url', $url);
        }

        $consoleIo = new ConsoleIo($input, $output, $this->getApplication()->getHelperSet());

        if ($input->getOption('server')) {
            $sculpin->run($dataSource, $sourceSet, $consoleIo);

            $docroot = $this->getContainer()->getParameter('sculpin.output_dir');
            $kernel = $this->getContainer()->get('kernel');

            $httpServer = new HttpServer(
                $output,
                $docroot,
                $kernel->getEnvironment(),
                $kernel->isDebug(),
                $input->getOption('port')
            );

            if ($watch) {
                $httpServer->addPeriodicTimer(1, function() use ($sculpin, $dataSource, $sourceSet, $consoleIo) {
                    clearstatcache();
                    $sourceSet->reset();

                    $sculpin->run($dataSource, $sourceSet, $consoleIo);
                });
            }

            $httpServer->run();
        } else {
            do {
                $sculpin->run($dataSource, $sourceSet, $consoleIo);

                if ($watch) {
                    sleep(2);
                    clearstatcache();
                    $sourceSet->reset();
                }
            } while ($watch);
        }
    }
}
