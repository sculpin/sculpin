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

use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputInterface;

use sculpin\console\Application;

use sculpin\Sculpin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface IBundle extends EventSubscriberInterface {
    
    /**
     * Initialize the bundle.
     */
    public function initBundle(Sculpin $sculpin);
    
    /**
     * Get a full path to a resource
     * @param string $partialPath
     */
    public function getResourcePath($partialPath);

    /**
     * Configure console application
     * @param Application $application
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    static public function CONFIGURE_CONSOLE_APPLICATION(Application $application, InputInterface $input, OutputInterface $output);

}