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
        $watch = (Boolean) $input->getOption('watch');
        $sculpin = $this->getContainer()->get('sculpin');
        $dataSource = $this->getContainer()->get('sculpin.data_source');
        $sourceSet = new SourceSet;

        do {
            $sculpin->run($dataSource, $sourceSet);

            if ($watch) {
                sleep(2);
                clearstatcache();
                $sourceSet->reset();
            }
        } while ($watch);
    }
}
