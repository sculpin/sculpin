<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Bundle;

use Sculpin\Core\Configuration\Configuration;
use Sculpin\Core\Sculpin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Abstract Sculpin Bundle.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
abstract class AbstractBundle extends Bundle implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array();
    }
}
