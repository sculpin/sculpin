<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\PostsBundle;

use Sculpin\Bundle\PostsBundle\DependencyInjection\Compiler\PostsMapPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Sculpin Posts Bundle.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinPostsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new PostsMapPass);
    }
}
