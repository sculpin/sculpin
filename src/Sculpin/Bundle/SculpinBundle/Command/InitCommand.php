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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Init Command - Initialize default website configuration and structure.
 */
class InitCommand extends AbstractCommand
{
    public const COMMAND_SUCCESS          = 0;
    public const PROJECT_FOLDER_NOT_EMPTY = 101;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $prefix = $this->isStandaloneSculpin() ? '' : 'sculpin:';

        $this
            ->setName($prefix.'init')
            ->setDescription('Initialize a default site configuration.')
            ->setDefinition([])
            ->setHelp(<<<EOT
The <info>init</info> command initializes a default site configuration.

EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $application = $this->getApplication();
        if ($application instanceof Application) {
            foreach ($application->getMissingSculpinBundlesMessages() as $message) {
                $output->writeln($message);
            }
        }

        $projectDir = $this->getContainer()->getParameter('sculpin.project_dir');
        $output->writeln('Project Directory: <info>' . $projectDir . '</info>');

        $output->writeln('Initializing <info>./app</info> and <info>./source</info> for Sculpin' . "\n");

        // Actions:

        // 1. Ensure we're operating on a clean slate
        if (!$this->ensureCleanSlate($projectDir, $output)) {
            $output->writeln('<error>This command can only be run in an uninitialized folder.</error>');

            return self::PROJECT_FOLDER_NOT_EMPTY;
        }

        // 2. Create default Kernel
        $this->createDefaultKernel($projectDir, $output);

        // 3. Create default Site config files
        $this->createSiteKernelFile($projectDir, $output);
        $this->createSiteConfigFile($projectDir, $output);

        // 4. Create source folder and very first basic entry in source folder
        $this->createSourceFolder($projectDir, $output);

        $output->writeln('<info>Success!</info>');
        $output->writeln('Run "sculpin generate --watch --server" to see your static site in action.');

        return self::COMMAND_SUCCESS;
    }

    protected function ensureCleanSlate(string $projectDir, OutputInterface $output): bool
    {
        $fs = new Filesystem();
        if ($fs->exists($projectDir . '/app')) {
            $output->writeln('<info>/app folder exists.</info>');

            return false;
        }

        if ($fs->exists($projectDir . '/source')) {
            $output->writeln('<info>/source folder exists.</info>');

            return false;
        }

        return true;
    }

    protected function createDefaultKernel(string $projectDir, OutputInterface $output): bool
    {
        $contents = <<<EOF
<?php

class SculpinKernel extends \Sculpin\Bundle\SculpinBundle\HttpKernel\AbstractKernel
{
    protected function getAdditionalSculpinBundles(): array
    {
        return [
//            'App\\Bundle\\ExampleBundle\\AppExampleBundle'
        ];
    }
}

EOF;
        $this->createFile($projectDir . '/app/SculpinKernel.php', $contents);

        return true;
    }

    protected function createSiteKernelFile(string $projectDir, OutputInterface $output): bool
    {
        $contents = <<<EOF
sculpin_content_types:
    posts:
      enabled: false

EOF;
        $this->createFile($projectDir . '/app/config/sculpin_kernel.yml', $contents);

        return true;
    }

    protected function createSiteConfigFile(string $projectDir, OutputInterface $output): bool
    {
        $contents = <<<EOF
title: My Sculpin Site
subtitle: A Static Site Powered By Sculpin
google_analytics_tracking_id: ''
url: ''

EOF;
        $this->createFile($projectDir . '/app/config/sculpin_site.yml', $contents);

        return true;
    }

    protected function createSourceFolder(string $projectDir, OutputInterface $output): bool
    {
        $fs = new Filesystem();

        $fs->dumpFile(
            $projectDir . '/source/index.md',
            <<<EOF
---
layout: default
---

<h1>Welcome to {{site.title}}</h1>

EOF
        );

        $fs->dumpFile(
            $projectDir . '/source/_views/default.html',
            <<<EOF
<html>
<head><title>{{site.title}}</title></head>
<body>
{% block content_wrapper %}{% block content '' %}{% endblock content_wrapper %}
</body>
</html>

EOF
        );

        return true;
    }

    protected function createFile(string $path, string $contents): void
    {
        $fs = new Filesystem();
        $fs->dumpFile($path, $contents);
    }
}
