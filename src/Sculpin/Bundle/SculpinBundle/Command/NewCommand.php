<?php

namespace Sculpin\Bundle\SculpinBundle\Command;

use Sculpin\Bundle\SculpinBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class NewCommand extends AbstractCommand
{
    /**
     * @var string
     */
    private $projectName;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $configDir;

    /**
     * @var string
     */
    private $sourceDir;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $prefix = $this->isStandaloneSculpin() ? '' : 'sculpin:';

        $this
            ->setName($prefix.'new')
            ->setDescription('Create a new site.')
            ->addArgument('name', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->projectName = $input->getArgument('name');

        $this->filesystem = $this->getContainer()->get('filesystem');

        if ($this->filesystem->exists($this->projectName)) {
            $output->writeln(sprintf("<error>Project %s already exists.</error>", $this->projectName));
        } else {
            $this->createDirectories();
            $this->createSculpinSiteFile();
            $this->createIndexFile();

            $output->writeln(sprintf("<info>Project %s created.</info>", $this->projectName));
        }
    }

    /**
     * Create the directories needed for a Sculpin site.
     */
    private function createDirectories()
    {
        $this->configDir = $this->projectName . DIRECTORY_SEPARATOR . 'app/config';
        $this->sourceDir = $this->projectName . DIRECTORY_SEPARATOR . 'source';

        $this->filesystem->mkdir([
            $this->configDir,
            $this->sourceDir,
        ]);
    }

    /**
     * Create an example sculpin_site.yml file.
     */
    private function createSculpinSiteFile()
    {
        $content = <<< CONTENT
title: My New Sculpin Site
CONTENT;

        $this->filesystem->dumpFile(
            $this->configDir . DIRECTORY_SEPARATOR . 'sculpin_site.yml',
            $content
        );
    }

    /**
     * Create an example index.md file.
     */
    private function createIndexFile()
    {
        $content = <<< CONTENT
---
---
Hello, world!
CONTENT;

        $this->filesystem->dumpFile(
            $this->sourceDir . DIRECTORY_SEPARATOR . 'index.md',
            $content
        );
    }
}
