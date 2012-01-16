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

use sculpin\Util;

use Symfony\Component\Finder\Finder;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    private function skeletonResourcesRoot()
    {
        return dirname(dirname(__DIR__)).'/resources/skeleton';
    }
    protected function configure()
    {
        $finder = new Finder();
        $skeletons = $finder
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->depth(0)
            ->directories()
            ->in($this->skeletonResourcesRoot());
        $skeletonList = '';
        foreach ($skeletons as $skeleton) {
            /* @var $skeleton \Symfony\Component\Finder\SplFileInfo */
            $skeletonList .= ' * ' . $skeleton->getFilename() ."\n";
        }

        $this
            ->setName('init')
            ->setDescription('Initialize a project.')
            ->setDefinition(array(
                new InputOption('skeleton', null, InputOption::VALUE_REQUIRED, 'Specify the skeleton (name or path) to use for the initialized site.', 'standard'),
            ))
            ->setHelp(<<<EOT
The <info>init</info> command initializes a project.

The following built-in skeletons are available:
{$skeletonList}
EOT
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $skeleton = $input->getOption('skeleton');
        if (file_exists($skeletonRoot = $this->skeletonResourcesRoot().'/'.$skeleton)) {
            $output->writeln('Initializing built-in <info>'.$skeleton.'</info> project');
        } elseif (file_exists($skeletonRoot = $skeleton)) {
            $output->writeln('Initializing custom project based on directory <info>'.$skeleton.'</info>');
        } else {
            $output->writeln('Specified skeleton <error>'.$skeleton.'</error> could not be found');
            return;
        }
        $finder = new Finder();
        $skeletonFiles = $finder
            ->files()
            ->in($skeletonRoot);
        $projectRoot='.'; // TODO: Make this configurable
        foreach ($skeletonFiles as $file) {
            /* @var $file \Symfony\Component\Finder\SplFileInfo */
            $parentDir = dirname($file->getRelativePathname());
            if ('.' != $parentDir) {
                Util::RECURSIVE_MKDIR($projectRoot.'/'.$parentDir);
            }
            $output->write('+ '.$file->getRelativePathname());
            copy($file, $projectRoot.'/'.$file->getRelativePathname());
            $output->writeln(' [done]');
        }
    }
}
