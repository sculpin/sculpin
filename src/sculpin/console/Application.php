<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\console;

use sculpin\configuration\Util;

use sculpin\configuration\Configuration;

use sculpin\configuration\YamlFileConfigurationBuilder;

use sculpin\Sculpin;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{

    /**
     * Configuration
     * @var \sculpin\configuration\Configuration
     */
    private $configuration;
    
    public function __construct()
    {
        parent::__construct('Sculpin', Sculpin::VERSION);
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->add(new command\InitCommand());
        $this->add(new command\GenerateCommand());
        foreach (Sculpin::GET_CONFIGURED_BUNDLES($this->configuration()) as $bundleClassName) {
            try {
                $obj = new \ReflectionClass($bundleClassName);
                if ($obj->hasMethod('CONFIGURE_CONSOLE_APPLICATION')) {
                    call_user_func(array($bundleClassName, 'CONFIGURE_CONSOLE_APPLICATION'), $this, $input, $output);
                }
            } catch (Exception $e) {
                // probably nothing...
            }
        }
        return parent::doRun($input, $output);
    }
    
    public function configuration()
    {
        if ($this->configuration === null) {
            $configurationFiles = array(
                __DIR__.'/../resources/configuration/sculpin.yml',
            );
            if (file_exists('sculpin.yml')) {
                $configurationFiles[] = 'sculpin.yml';
            } elseif (file_exists('sculpin.yml.dist')) {
                $configurationFiles[] = 'sculpin.yml.dist';
            }
            $configurationBuilder = new YamlFileConfigurationBuilder($configurationFiles);
            $this->configuration = $configurationBuilder->build();
        }
        return $this->configuration;
    }

    public function createSculpin()
    {
        return new Sculpin($this->configuration());
    }

}
