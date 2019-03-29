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

/**
 * Twig Environment with Loaded Template Invalidation.
 */
class Environment extends \Twig\Environment
{
    /**
     * We aren't supposed to be using loadTemplate directly, and Twig V2 took away our ability to invalidate it.
     *
     * Hacky workaround to expose invalidation.
     */
    public function invalidateLoadedTemplates(): void
    {
        try {
            $me     = new \ReflectionClass($this);
            $parent = $me;

            while ($parent instanceof \ReflectionClass) {
                $me     = $parent;
                $parent = $me->getParentClass();
            }

            $templates = $me->getProperty('loadedTemplates');
            $templates->setAccessible(true);
            $templates->setValue($this, []);
        } catch (\ReflectionException $e) {
        }
    }
}
