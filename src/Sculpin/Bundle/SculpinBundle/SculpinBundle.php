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

namespace Sculpin\Bundle\SculpinBundle;

use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\ConverterManagerPass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\DataSourcePass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\DataProviderManagerPass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\EventSubscriberOverridePass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\FormatterManagerPass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\GeneratorManagerPass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\PathConfiguratorPass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\CustomMimeTypesRepositoryPass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\DirectoryOverridePass;
use Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler\WriterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ConverterManagerPass);
        $container->addCompilerPass(new DataProviderManagerPass);
        $container->addCompilerPass(new FormatterManagerPass);
        $container->addCompilerPass(new GeneratorManagerPass);
        $container->addCompilerPass(new PathConfiguratorPass);
        $container->addCompilerPass(new CustomMimeTypesRepositoryPass);
        $container->addCompilerPass(new DataSourcePass);
        $container->addCompilerPass(new WriterPass);
        $container->addCompilerPass(new AddConsoleCommandPass);
        $container->addCompilerPass(new DirectoryOverridePass);
        $container->addCompilerPass(new EventSubscriberOverridePass);
    }
}
