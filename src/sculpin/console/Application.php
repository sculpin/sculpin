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

use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use sculpin\configuration\Util;
use sculpin\configuration\Configuration;
use sculpin\configuration\YamlFileConfigurationBuilder;
use sculpin\Sculpin;

class Application extends BaseApplication
{

    /**
     * Configuration
     * @var \sculpin\configuration\Configuration
     */
    private $configuration;
    
    /**
     * Class Loader
     * @var ClassLoader
     */
    private $classLoader;
    
    /**
     * Default value for project root.
     * @var unknown_type
     */
    const DEFAULT_PROJECT_ROOT = '.';
    
    public function __construct(ClassLoader $classLoader)
    {
        parent::__construct('Sculpin', Sculpin::VERSION);
        $this->getDefinition()->addOption(new InputOption('project-root', null, InputOption::VALUE_REQUIRED, 'Project root.', self::DEFAULT_PROJECT_ROOT));
        $this->classLoader = $classLoader;
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $projectRoot = $input->getParameterOption('--project-root') ?: self::DEFAULT_PROJECT_ROOT;
        $obj = new \ReflectionClass($this->classLoader);
        if ($autoloadNamespacesFile = realpath($projectRoot.'/vendor/.composer/autoload_namespaces.php')) {
            if (dirname($obj->getFileName()) != dirname($autoloadNamespacesFile)) {
                // We have an autoload file that is *not* the same as the
                // autoload that bootstrapped this application.
                $map = require $autoloadNamespacesFile;
                foreach ($map as $namespace => $path) {
                    $this->classLoader->add($namespace, $path);
                }
            }
        }
        //if (realpath($projectRoot.'/vendor/autoload.php') != realpath())
        $this->initializeConfiguration(realpath($projectRoot));
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
        $this->add(new command\ConfigurationDumpCommand());
        $this->add(new command\GenerateCommand());
        $this->add(new command\InitCommand());
        return parent::doRun($input, $output);
    }
        
    /**
     * Get Sculpin configuration
     * @throws \RuntimeException
     * @return \sculpin\configuration\Configuration
     */
    public function configuration()
    {
        if ($this->configuration === null) {
            throw new \RuntimeException("Configuration has not been initialized.");
        }
        return $this->configuration;
    }

    /**
     * Create an instance of Sculpin
     * @return \sculpin\Sculpin
     */
    public function createSculpin()
    {
        return new Sculpin($this->configuration());
    }
    
    /**
     * Initialize Sculpin configuration
     * @throws \RuntimeException
     * @param unknown_type $projectRoot
     */
    private function initializeConfiguration($projectRoot)
    {
        if (null !== $this->configuration) {
            throw new \RuntimeException("Configuration requested before configuration has been initialized");
        }
        $configurationBuilder = new YamlFileConfigurationBuilder(array(
                __DIR__.'/../resources/configuration/sculpin.yml',
                $projectRoot.'/sculpin.yml.dist',
                $projectRoot.'/sculpin.yml',
        ));
        $this->configuration = $configurationBuilder->build();
        $this->configuration->set('project_root', $projectRoot);
        $source = realpath($this->configuration->getPath('source'));
        $this->configuration->set('source_is_project_root', $source == $projectRoot);
    }
    
}
