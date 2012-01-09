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

use sculpin\configuration\Configuration;
use sculpin\configuration\YamlConfigurationBuilder;
use sculpin\event\Event;
use sculpin\event\SourceFilesChangedEvent;
use sculpin\formatter\IFormatter;
use sculpin\source\SourceFile;
use sculpin\source\SourceFileSet;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Symfony\Component\Finder\Finder;

use dflydev\util\antPathMatcher\IAntPathMatcher;
use dflydev\util\antPathMatcher\AntPathMatcher;

class Sculpin {
    
    const VERSION = '@package_version@';
    const EVENT_BEFORE_START = 'sculpin.core.beforeStart';
    const EVENT_CONFIGURE_BUNDLES = 'sculpin.core.configureBundles';
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
     * Twig
     * @var \Twig_Environment
     */
    protected $twig;
    
    /**
     * Bundles (by name)
     * @var array
     */
    protected $bundles = array();
    
    /**
     * List of additional exclusions
     * @var array
     */
    protected $exclusions = array();
    
    /**
     * Registered formatters
     * @var array
     */
    protected $formatters = array();
    
    /**
     * Name of the default formatter to use.
     * @var string
     */
    protected $defaultFormatter;
    
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
        foreach (array_merge($this->configuration->get('core_exclude'), $this->configuration->get('exclude')) as $pattern) {
            $this->exclude($pattern);
        }
    }
    
    /**
     * Starts up Sculpin
     * 
     * This process is called to initialize plugins
     */
    public function start()
    {
        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_START, new Event($this));
        foreach ($this->configuration->get('core_bundles') as $bundleClassName) {
            if (!in_array($bundleClassName, $this->configuration->get('disabled_core_bundles'))) {
                // Add core bundles if they are not disabled.
                $this->addBundle($bundleClassName);
            }
        }
        foreach ($this->configuration->get('bundles') as $bundleClassName) {
            // Add 3rd party bundles.
            $this->addBundle($bundleClassName);
        }
        $this->eventDispatcher->dispatch(self::EVENT_CONFIGURE_BUNDLES, new Event($this));
        $this->eventDispatcher->dispatch(self::EVENT_AFTER_START, new Event($this));
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
                foreach ($this->exclusions as $pattern) {
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
                        $this->inputFiles[$file->getPathname()] = $updatedFiles[] = new SourceFile($file);
                    } else {
                        $unchangedFiles[] = new SourceFile($file);
                    }
                } else {
                    // File is new.
                    $this->inputFiles[$file->getPathname()] = $newFiles[] = new SourceFile($file);
                }
            }

            $inputFileSet = new SourceFileSet($newFiles, $updatedFiles, $unchangedFiles);

            if ( $inputFileSet->hasChangedFiles() ) {
                $inputFilesChangedEvent = new SourceFilesChangedEvent($this, $inputFileSet);
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
                foreach ($inputFileSet->changedFiles() as $inputFile) {
                    /* @var $inputFile SourceFile */
                    //$this->formatPage($inputFile->content(), $inputFile->context()) . "\n";
                }
                $this->eventDispatcher->dispatch(
                    self::EVENT_AFTER_FORMAT,
                    $inputFilesChangedEvent
                );
                
                foreach ($inputFileSet->changedFiles() as $inputFile) {
                    /* @var $inputFile SourceFile */
                    if ($inputFile->isNormal()) {
                        // Do do something with normal files.
                    }
                }
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
    
    /**
     * Configuration
     * @return \sculpin\configuration\Configuration
     */
    public function configuration()
    {
        return $this->configuration;
    }
    
    /**
     * Matcher
     * @return \dflydev\util\antPathMatcher\IAntPathMatcher
     */
    public function matcher()
    {
        return $this->matcher;
    }
    
    protected function addBundle($bundleClassName)
    {
        if (!preg_match('/(\w+?)(|Bundle)$/', $bundleClassName, $matches)){
            throw new \RuntimeException("Could not determine bundle name for class '$bundleClassName'");
        }
        $bundle = new $bundleClassName();
        $bundle->initBundle($this);
        $this->eventDispatcher->addSubscriber($bundle);
        $this->bundles[$matches[1]] = $bundle;
    }

    /**
     * Exclude a pattern
     * @param string $pattern
     */
    public function exclude($pattern)
    {
        if (!in_array($pattern, $this->exclusions)) {
            $this->exclusions[] = $pattern;
        }
    }
    
    public function formatBlocks($template, $context)
    {
        $context = $this->buildFormatContext($context);
        return $this->formatter($context['formatter'])->formatBlocks($this, $template, $context);
    }
    
    public function formatPage($template, $context)
    {
        $context = $this->buildFormatContext($context);
        return $this->formatter($context['formatter'])->formatPage($this, $template, $context);
    }
    
    public function registerFormatter($name, IFormatter $formatter)
    {
        $this->formatters[$name] = $formatter;
        if (!$this->defaultFormatter) {
            $this->defaultFormatter = $name;
        }
    }
    
    /**
     * Get formatter
     * @param string $name
     * @return IFormatter
     */
    public function formatter($name)
    {
        // TODO: Throw an exception of the requested formatter does not exist?
        return isset($this->formatters[$name]) ? $this->formatters[$name] : null;
    }
    
    public function buildFormatContext($pageContext)
    {
        $context = Util::MERGE_ASSOC_ARRAY($this->buildDefaultFormatContext(), array('page' => $pageContext));
        foreach (array('layout', 'formatter') as $key) {
            if (isset($pageContext[$key])) {
                $context[$key] = $pageContext[$key];
            }
        }
        return $context;
    }

    public function buildDefaultFormatContext()
    {
        $defaultContext = $this->configuration->export();
        $defaultContext['formatter'] = $this->defaultFormatter;
        return $defaultContext;
    }
    
}
