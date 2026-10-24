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

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run sculpin: Generate, Serve, and Edit the site.
 *
 * @author Kevin Boyd <kevin@whateverthing.com>
 */
class RunCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('run')
            ->setDescription('Run sculpin: generate, serve, and edit your unpublished site.')
            ->setDefinition([
                new InputOption(
                    'clean',
                    null,
                    InputOption::VALUE_NONE,
                    'Cleans the output directory prior to generation.'
                ),
                new InputOption('url', null, InputOption::VALUE_REQUIRED, 'Override URL.'),
                new InputOption('port', null, InputOption::VALUE_REQUIRED, 'Port'),
                new InputOption('no-editor', null, InputOption::VALUE_NONE, 'Turn off the in-browser editor.'),
                new InputOption('output-dir', null, InputOption::VALUE_REQUIRED, 'Output Directory'),
                new InputOption('source-dir', null, InputOption::VALUE_REQUIRED, 'Source Directory'),
            ])
            ->setHelp(<<<EOT
            The <info>run</info> command is a helper alias for launching Sculpin.

            It is the same as running <info>sculpin generate --watch --server --editor</info>

            It will watch your <info>--source-dir</info> for changes, automatically
            regenerate the content into <info>--output-dir</info>, and provide an in-browser
            editor interface for ease of use.

            If your site has not yet been initialized, please
            run <warning> sculpin init </warning> before <info>sculpin run</info>.
            EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputData = [
            'command' => 'generate',
            '--watch' => true,
            '--editor' => true,
            '--server' => true,
        ];

        foreach ($input->getOptions() as $key => $value) {
            $inputData['--' . $key] = $value;
        }

        if (true === $inputData['--no-editor']) {
            unset($inputData['--editor']);
            unset($inputData['--no-editor']);
        }

        $inputData = $inputData + $input->getArguments();
        $inputData = array_filter($inputData);

        $commandInput = new ArrayInput($inputData);

        return $this->getApplication()->doRun($commandInput, $output);
    }
}
