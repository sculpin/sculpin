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

namespace Sculpin\Bundle\TwigBundle;

use Sculpin\Bundle\TwigBundle\DependencyInjection\Compiler\TwigEnvironmentPass;
use Sculpin\Bundle\TwigBundle\DependencyInjection\Compiler\TwigLoaderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class SculpinTwigBundle extends Bundle
{
    public const FORMATTER_NAME = 'twig';

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TwigEnvironmentPass);
        $container->addCompilerPass(new TwigLoaderPass);
    }
}
