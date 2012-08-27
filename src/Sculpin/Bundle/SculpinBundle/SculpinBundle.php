<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle;

use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\ConverterManagerPass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\DataProviderManagerPass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\FormatterManagerPass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\PathConfiguratorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Framework Bundle.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConverterManagerPass);
        $container->addCompilerPass(new DataProviderManagerPass);
        $container->addCompilerPass(new FormatterManagerPass);
        $container->addCompilerPass(new PathConfiguratorPass);
    }
}
