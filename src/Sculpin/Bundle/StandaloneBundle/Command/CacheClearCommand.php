<?php

declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\StandaloneBundle\Command;

use Sculpin\Core\Console\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clear and Warmup the cache.
 *
 * Originally from FrameworkBundle/Command/CacheClearCommand.php
 *
 * @author Francis Besset <francis.besset@gmail.com>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CacheClearCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clears the cache')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command clears the application cache for a given environment
and debug mode:

<info>php %command.full_name% --env=dev</info>
<info>php %command.full_name% --env=prod --no-debug</info>

EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheDir = $this->getContainer()->getParameter('kernel.cache_dir');
        $filesystem   = $this->getContainer()->get('filesystem');

        if ($filesystem->exists($cacheDir)) {
            if (!is_writable($cacheDir)) {
                throw new \RuntimeException(sprintf('Unable to write in the "%s" directory', $cacheDir));
            }

            $filesystem->remove($cacheDir);
        }

        return 0;
    }
}
