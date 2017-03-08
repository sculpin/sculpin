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
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
                new InputOption(
                    'clean',
                    null,
                    InputOption::VALUE_NONE,
                    'Cleans the output directory prior to generation.'
                ),
                new InputOption(
                    'watch',
                    null,
                    InputOption::VALUE_NONE,
                    'Watch source and regenerate site as changes are made.'
                ),
                new InputOption(
                    'server',
                    null,
                    InputOption::VALUE_NONE,
                    'Start an HTTP server to host your generated site'
                ),
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

        $docroot = $this->getContainer()->getParameter('sculpin.output_dir');
        if ($input->getOption('clean')) {
            $this->clean($input, $output, $docroot);
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

            $kernel = $this->getContainer()->get('kernel');

            $httpServer = new HttpServer(
                $output,
                $docroot,
                $kernel->getEnvironment(),
                $kernel->isDebug(),
                $input->getOption('port')
            );

            if ($watch) {
                $httpServer->addPeriodicTimer(1, function () use ($sculpin, $dataSource, $sourceSet, $consoleIo) {
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

    /**
     * Cleanup an output directory by deleting it.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @param string          $dir    The directory to remove
     */
    protected function clean(InputInterface $input, OutputInterface $output, $dir)
    {
        $fileSystem = $this->getContainer()->get('filesystem');
        if ($fileSystem->exists($dir)) {
            if ($input->isInteractive()) {
                // Prompt the user for confirmation.
                /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion(sprintf(
                    'Are you sure you want to delete all the contents of the %s directory?',
                    $dir
                ), false);
                if (!$helper->ask($input, $output, $question)) {
                    return;
                }
            }
            $output->writeln(sprintf('Deleting %s', $dir));
            $fileSystem->remove($dir);
        }
    }
}
