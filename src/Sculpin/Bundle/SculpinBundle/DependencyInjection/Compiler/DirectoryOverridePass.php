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

namespace Sculpin\Bundle\SculpinBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Kevin Boyd
 */
class DirectoryOverridePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (true === $container->hasParameter('sculpin.source_dir_override')) {
            $override = $container->getParameter('sculpin.source_dir_override');

            if ($override) {
                $container->setParameter(
                    'sculpin.source_dir',
                    '%sculpin.project_dir%/%sculpin.source_dir_override%'
                );
            }
        }

        if (true === $container->hasParameter('sculpin.output_dir_override')) {
            $override = $container->getParameter('sculpin.output_dir_override');

            if ($override) {
                $container->setParameter(
                    'sculpin.output_dir',
                    '%sculpin.project_dir%/%sculpin.output_dir_override%'
                );
            }
        }
    }
}
