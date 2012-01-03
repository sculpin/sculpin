<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin;

use sculpin\configuration\YamlConfigurationBuilder;

use sculpin\event\InputFilesChangedEvent;

use Symfony\Component\EventDispatcher\EventDispatcher;

use dflydev\util\antPathMatcher\IAntPathMatcher;

use dflydev\util\antPathMatcher\AntPathMatcher;

use Symfony\Component\Finder\Finder;

use sculpin\configuration\Configuration;

class Sculpin {
    
    const VERSION = '@package_version@';
    const EVENT_BEFORE_START = 'sculpin.core.beforeStart';
    const EVENT_AFTER_START = 'sculpin.core.afterStart';
    const EVENT_BEFORE_RUN = 'sculpin.core.beforeRun';
    const EVENT_AFTER_RUN = 'sculpin.core.afterRun';
    const EVENT_BEFORE_STOP = 'sculpin.core.beforeStop';
    const EVENT_AFTER_STOP = 'sculpin.core.afterStop';
    const EVENT_INPUT_FILES_CHANGED = 'sculpin.core.inputFilesChanged'; // TODO: Needed?
    const EVENT_BEFORE_GENERATE = 'sculpin.core.beforeGenerate';
    const EVENT_GENERATE = 'sculpin.core.generate';
    const EVENT_AFTER_GENERATE = 'sculpin.core.afterGenerate';
    const EVENT_BEFORE_CONVERT = 'sculpin.core.beforeConvert';
    const EVENT_CONVERT = 'sculpin.core.convert';
    const EVENT_AFTER_CONVERT = 'sculpin.core.afterConvert';
    const EVENT_BEFORE_FORMAT = 'sculpin.core.beforeFormat';
    const EVENT_FORMAT = 'sculpin.core.format';
    const EVENT_AFTER_FORMAT = 'sculpin.core.afterFormat';
    
    /**
     * Configuration
     * @var sculpin\configuration\Configuration
     */
    protected $configuration;
    
    /**
     * Event Dispatcher
     * @var Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $eventDispatcher;
    
    /**
     * Finder
     * @var Symfony\Component\Finder\Finder
     */
    protected $finder;
    
    /**
     * Matcher
     * @var dflydev\util\antPathMatcher\IAntPathMatcher
     */
    protected $matcher;

    /**
     * List of all know input files
     * @var array
     */
    protected $inputFiles;
    
    /**
     * Constructor
     * @param Configuration $configuration
     * @param EventDispatcher $eventDispatcher
     * @param Finder $finder
     * @param IAntPathMatcher $matcher
     */
    public function __construct(Configuration $configuration, EventDispatcher $eventDispatcher = null, Finder $finder = null, IAntPathMatcher $matcher = null)
    {
        $this->configuration = $configuration;
        $this->eventDispatcher = $eventDispatcher !== null ? $eventDispatcher : new EventDispatcher();
        $this->finder = $finder !== null ? $finder : new Finder();
        $this->matcher = $matcher !== null ? $matcher : new AntPathMatcher();
    }
    
    /**
     * Starts up Sculpin
     * 
     * This process is called to initialize plugins
     */
    public function start()
    {
        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_START);
        foreach ($this->configuration->get('core_bundles') as $bundleClassName) {
            if (!in_array($bundleClassName, $this->configuration->get('disabled_core_bundles'))) {
                $this->eventDispatcher->addSubscriber(new $bundleClassName());
            }
        }
        $this->eventDispatcher->dispatch(self::EVENT_AFTER_START);
    }
    
    public function run($watch)
    {

        // Allow for cleanup to happen in case control-c is detected.
        declare(ticks = 1);
        $sculpin = $this;
        pcntl_signal(SIGINT, function() use ($sculpin) {
            // We are no longer running.
            $sculpin->running = false;
        });

        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_RUN);
        $this->running = true;

        while ($this->running) {
            
            // This is where the work should get done.
            
            // Get all of the files in the site.
            $files = array();

            // Trigger before all files processing
            $allFiles = $this->finder->files()->ignoreVCS(true)->in($this->configuration->getPath('source'));
            foreach ( $allFiles as $file ) {
                foreach (array_merge($this->configuration->get('core_exclude'), $this->configuration->get('exclude')) as $pattern) {
                    if ($this->matcher->match($pattern, $file->getRelativePathname())) {
                        continue 2;
                    }
                }
                $files[] = $file;
            }
            
            $newFiles = array();
            $updatedFiles = array();
            $unchangedFiles = array();
            foreach ($files as $file) {
                if (isset($this->inputFiles[$file->getPathname()])) {
                    // File existed before.
                    if ($file->getCTime()>$this->inputFiles[$file->getPathname()]->getCTime()) {
                        // TODO: Maybe also check sum?
                        $this->inputFiles[$file->getPathname()] = $updatedFiles[] = new InputFile($file);
                    } else {
                        $unchangedFiles[] = new InputFile($file);
                    }
                } else {
                    // File is new.
                    $this->inputFiles[$file->getPathname()] = $newFiles[] = new InputFile($file);
                }
            }

            if ( count($newFiles) or count($updatedFiles) ) {
                $inputFilesChangedEvent = new InputFilesChangedEvent($this, $newFiles, $updatedFiles, $unchangedFiles);
                $this->eventDispatcher->dispatch(
                    self::EVENT_INPUT_FILES_CHANGED,
                    $inputFilesChangedEvent
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_BEFORE_GENERATE,
                    $inputFilesChangedEvent
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_GENERATE,
                    $inputFilesChangedEvent
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_AFTER_GENERATE,
                    $inputFilesChangedEvent
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_BEFORE_CONVERT,
                    $inputFilesChangedEvent
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_CONVERT,
                    $inputFilesChangedEvent
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_AFTER_CONVERT,
                    $inputFilesChangedEvent
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_BEFORE_FORMAT,
                    $inputFilesChangedEvent
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_FORMAT,
                    $inputFilesChangedEvent
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_AFTER_FORMAT,
                    $inputFilesChangedEvent
                );
            }

            if ($watch) {
                // Temporary.
                sleep(5);
            } else {
                $this->running = false;
            }

        }

        $this->eventDispatcher->dispatch(self::EVENT_AFTER_RUN);
        
    }
    
    public function stop()
    {
        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_STOP);
        $this->eventDispatcher->dispatch(self::EVENT_AFTER_STOP);
    }
    
    public function configuration()
    {
        return $this->configuration;
    }

}
