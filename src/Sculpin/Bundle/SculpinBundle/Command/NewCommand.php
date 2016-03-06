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
        $this->filesystem = new Filesystem();
        if (!$this->filesystem->exists($this->projectName)) {
            $this->createDirectories();
            $this->createSculpinSiteFile();
            $this->createIndexFile();
        }

        $output->writeln("Project $this->projectName created.");
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
        $content = [];
        $content[] = '---';
        $content[] = 'title: My New Sculpin Site';
        $this->filesystem->dumpFile(
            $this->configDir . DIRECTORY_SEPARATOR . 'sculpin_site.yml',
            implode(PHP_EOL, $content)
        );
    }

    /**
     * Create an example index.md file.
     */
    private function createIndexFile()
    {
        $content = [];
        $content[] = '---';
        $content[] = '---';
        $content[] = '# {{ site.title }}';
        $content[] = PHP_EOL;
        $content[] = 'Hello, world!';
        $this->filesystem->dumpFile(
            $this->sourceDir . DIRECTORY_SEPARATOR . 'index.md',
            implode(PHP_EOL, $content)
        );
    }
}
