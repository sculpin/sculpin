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

namespace Sculpin\Bundle\ThemeBundle\Command;

use Sculpin\Core\Console\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class ListCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('theme:list')
            ->setDescription('List currently installed themes.')
            ->setHelp(<<<EOT
The <info>theme:list</info> command lists currently installed themes.

EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $themeRegistry = $this->getContainer()->get('sculpin_theme.theme_registry');
        $activeTheme = $themeRegistry->findActiveTheme();
        $themes = $themeRegistry->listThemes();

        foreach ($themes as $theme) {
            if ($theme['name'] === $activeTheme['name']) {
                $themeOutput = '<info>'.$theme['name'].'</info> *';
            } else {
                $themeOutput = $theme['name'];
            }

            if (isset($theme['parent'])) {
                $themeOutput .= ' (child of '.$theme['parent'].')';
            }

            if (preg_match('/^(.+?)-dev$/', $theme['name'], $matches)) {
                $themeOutput .= ' :: '.$matches[1].'';
            }
            $output->writeln($themeOutput);
        }

        return 0;
    }
}
