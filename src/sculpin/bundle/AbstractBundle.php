<?php

/*
 * This file is a part of Sculpin
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle;

use sculpin\Sculpin;
use sculpin\configuration\YamlFileConfigurationBuilder;
use sculpin\console\Application;
use sculpin\event\Event;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractBundle extends Bundle {

    /**
     * (non-PHPdoc)
     */
    public function build(ContainerBuilder $sculpin)
    {
        $defaultBundleConfiguration = $this->getPath() . '/resources/configuration/sculpin.yml';
        if (file_exists($defaultBundleConfiguration)) {
            // If the bundle has a sculpin.yml configuration file it should be
            // read and imported into the Sculpin configuration. We do not want
            // our imported configuration to clobber the existing configuration
            // values, tho. (since user overrides will have already been read)
            $configurationBuilder = new YamlFileConfigurationBuilder(array($defaultBundleConfiguration));
            $sculpin->get('sculpin.configuration')->import($configurationBuilder->build(), false);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\EventDispatcher.EventSubscriberInterface::getSubscribedEvents()
     */
    static function getSubscribedEvents()
    {
        $events = static::getBundleEvents();
        $coreEvents = array(
            Sculpin::EVENT_CONFIGURE_BUNDLES => 'preConfigureBundle',
        );
        return $events ? array_merge($coreEvents, $events) : $coreEvents;
    }

    /**
     * Get events that the bundle cares about
     * 
     * Bundles should override this to direct Sculpin to send events
     * that they are interested in.
     * @see Symfony\Component\EventDispatcher.EventSubscriberInterface::getSubscribedEvents()
     * @return array
     */
    static function getBundleEvents()
    {
        return array();
    }
    
    /**
     * Configure the bundle
     * 
     * Called automatically by Sculpin after all bundles have been installed.
     * @param Sculpin $sculpin
     */
    public function configureBundle(Sculpin $sculpin)
    {
        // noop
    }

    /**
     * Preliminary bundle configuration. Internal use only.
     * @param Event $event
     */
    public function preConfigureBundle(Event $event)
    {
        $this->configureBundle($event->sculpin());
    }
    
}
