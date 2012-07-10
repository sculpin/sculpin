<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class AbstractBundle extends Bundle implements EventSubscriberInterface
{
    /**
     * The Sculpin object.
     *
     * @var Sculpin
     */
    protected $sculpin;
    protected $configuration;

    /**
     * {@inheritDoc}
     *
     * Maybe final is too far?
     */
    final public function build(ContainerBuilder $container)
    {
        // Extract objects that are required from the container.
        $this->sculpin = $container->get('sculpin');
        $this->configuration = $container->get('sculpin.configuration');
        $this->buildBundle();
    }

    /**
     * Build the bundle
     *
     * $this->sculpin, $this->configuration, and $this->container
     * are all available.
     */
    public function buildBundle()
    {
    }

    /**
     * {@inheritDoc}
     */
    static function getSubscribedEvents()
    {
        return array();
    }
}
