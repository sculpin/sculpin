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

namespace Sculpin\Bundle\EditorBundle;

use Sculpin\Bundle\ContentTypesBundle\DependencyInjection\Compiler\MapPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Sculpin Editor Bundle.
 *
 * @author Kevin Boyd <kevin@whateverthing.com>
 */
class SculpinEditorBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MapPass);
    }
}
