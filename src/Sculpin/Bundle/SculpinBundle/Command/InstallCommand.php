<?php declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\Command;

use Dflydev\EmbeddedComposer\Console\Command\InstallCommand as BaseInstallCommand;
use Sculpin\Bundle\StandaloneBundle\SculpinStandaloneBundle;

/**
 * Install Command.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class InstallCommand extends BaseInstallCommand
{
    /**
     * {@inheritdoc}
     */
    public function __construct($commandPrefix = 'sculpin:')
    {
        $prefix = class_exists(SculpinStandaloneBundle::class, false)
            ? ''
            : $commandPrefix;

        parent::__construct($prefix);
    }
}
