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

use Sculpin\Bundle\SculpinBundle\Console\Application;
use Sculpin\Bundle\SculpinBundle\HttpServer\HttpServer;
use Sculpin\Core\Io\ConsoleIo;
use Sculpin\Core\Io\IoInterface;
use Sculpin\Core\Sculpin;
use Sculpin\Core\Source\DataSourceInterface;
use Sculpin\Core\Source\SourceSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Exception\IOException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Generate the site.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class GenerateCommand extends AbstractCommand
{
    /**
     * @var bool
     */
    protected $throwExceptions;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $prefix = $this->isStandaloneSculpin() ? '' : 'sculpin:';

        $this
            ->setName($prefix.'generate')
            ->setDescription('Generate a site from source.')
            ->setDefinition([
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
                new InputOption('output-dir', null, InputOption::VALUE_REQUIRED, 'Output Directory'),
                new InputOption('source-dir', null, InputOption::VALUE_REQUIRED, 'Source Directory'),
            ])
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
        $application = $this->getApplication();
        if ($application instanceof Application) {
            foreach ($application->getMissingSculpinBundlesMessages() as $message) {
                $output->writeln($message);
            }
        }

        $writer  = $this->getContainer()->get('sculpin.writer');
        $docroot = $writer->getOutputDir();
        if ($input->getOption('clean')) {
            $this->clean($input, $output, $docroot);
        }

        $watch = $input->getOption('watch') ?: false;
        $sculpin = $this->getContainer()->get('sculpin');
        $dataSource = $this->getContainer()->get('sculpin.data_source');
        $sourceSet = new SourceSet();

        $config = $this->getContainer()->get('sculpin.site_configuration');
        if ($url = $input->getOption('url')) {
            $config->set('url', $url);
        }

        $consoleIo = new ConsoleIo($input, $output);

        if ($input->getOption('server')) {
            $this->throwExceptions = false;
            $output->isDebug();
            $this->runSculpin($sculpin, $dataSource, $sourceSet, $consoleIo);

            $kernel = $this->getContainer()->get('kernel');

            $httpServer = new HttpServer(
                $output,
                $docroot,
                $kernel->getEnvironment(),
                $kernel->isDebug(),
                (int) $input->getOption('port')
            );

            if ($watch) {
                $httpServer->addPeriodicTimer(1, function () use ($sculpin, $dataSource, $sourceSet, $consoleIo) {
                    clearstatcache();
                    $sourceSet->reset();

                    $this->runSculpin($sculpin, $dataSource, $sourceSet, $consoleIo);
                });
            }

            $httpServer->run();
        } else {
            $this->throwExceptions = !$watch;
            do {
                $this->runSculpin($sculpin, $dataSource, $sourceSet, $consoleIo);

                if ($watch) {
                    sleep(2);
                    clearstatcache();
                    $sourceSet->reset();
                }
            } while ($watch);
        }

        return 0;
    }

    /**
     * Cleanup an output directory by deleting it.
     *
     * @param string $dir The directory to remove
     */
    private function clean(InputInterface $input, OutputInterface $output, string $dir): void
    {
        $fileSystem = $this->getContainer()->get('filesystem');

        if ($fileSystem->exists($dir)) {
            if ($input->isInteractive()) {
                // Prompt the user for confirmation.
                /** @var QuestionHelper $helper */
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

    /**
     * @throws \Throwable
     */
    protected function runSculpin(
        Sculpin $sculpin,
        DataSourceInterface $dataSource,
        SourceSet $sourceSet,
        IoInterface $io
    ) {
        $messages = [];
        $errPrint = function (\Throwable $e) {
            return $e->getMessage().PHP_EOL.' at '.str_replace(getcwd().DIRECTORY_SEPARATOR, '', $e->getFile());
        };

        try {
            $sculpin->run($dataSource, $sourceSet, $io);

            return;
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            $messages[] = '<error>Twig exception: ' . $errPrint($e) . '</error>';
        } catch (IOException $e) {
            $messages[] = '<error>Filesystem exception: ' . $errPrint($e) . '</error>';
        } catch (\Throwable $e) {
            $messages[] = '<error>Exception: ' . $errPrint($e) . '</error>';
        }

        if ($this->throwExceptions) {
            throw $e;
        }

        if ($io->isDebug()) {
            $messages[] = '<comment>Exception trace:</comment>';

            foreach ($e->getTrace() as $trace) {
                $messages[] = sprintf(
                    '<comment>  %s at %s:%s</comment>',
                    isset($trace['class']) ? $trace['class'] . '->' . $trace['function'] : $trace['function'],
                    $trace['file'],
                    $trace['line']
                );
            }
        }

        $io->write('<error>[FAILED]</error>');
        foreach ($messages as $message) {
            $io->write($message);
        }
        $io->write('');
    }
}
