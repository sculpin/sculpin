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

use sculpin\output\IOutput;
use sculpin\output\Writer;

use sculpin\output\SourceFileOutput;

use sculpin\converter\SourceFileConverterContext;

use sculpin\configuration\Configuration;
use sculpin\configuration\YamlConfigurationBuilder;
use sculpin\converter\IConverter;
use sculpin\event\ConvertSourceFileEvent;
use sculpin\event\Event;
use sculpin\event\SourceFilesChangedEvent;
use sculpin\event\FormatEvent;
use sculpin\formatter\IFormatter;
use sculpin\formatter\FormatContext;
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
    const EVENT_SOURCE_FILES_CHANGED = 'sculpin.core.inputFilesChanged'; // TODO: Needed?
    const EVENT_BEFORE_GENERATE = 'sculpin.core.beforeGenerate';
    const EVENT_GENERATE = 'sculpin.core.generate';
    const EVENT_AFTER_GENERATE = 'sculpin.core.afterGenerate';
    const EVENT_BEFORE_CONVERT = 'sculpin.core.beforeConvert';
    const EVENT_CONVERT = 'sculpin.core.convert';
    const EVENT_AFTER_CONVERT = 'sculpin.core.afterConvert';
    const EVENT_CONVERTED = 'sculpin.core.converted';
    const EVENT_BEFORE_FORMAT = 'sculpin.core.beforeFormat';
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
     * Writer
     * @var \sculpin\output\Writer
     */
    protected $writer;

    /**
     * List of all know input files
     * @var array
     */
    protected $sourceFiles;
    
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
     * Registered converters
     * @var array
     */
    protected $converters = array();

    /**
     * Constructor
     * @param Configuration $configuration
     * @param EventDispatcher $eventDispatcher
     * @param Finder $finder
     * @param IAntPathMatcher $matcher
     */
    public function __construct(Configuration $configuration, EventDispatcher $eventDispatcher = null, Finder $finder = null, IAntPathMatcher $matcher = null, Writer $writer = null)
    {
        $this->configuration = $configuration;
        $this->eventDispatcher = $eventDispatcher !== null ? $eventDispatcher : new EventDispatcher();
        $this->finder = $finder !== null ? $finder : new Finder();
        $this->matcher = $matcher !== null ? $matcher : new AntPathMatcher();
        $this->writer = $writer !== null ? $writer : new Writer();
        foreach (array_merge($this->configuration->get('core_exclude'), $this->configuration->get('exclude')) as $pattern) {
            $this->exclude($pattern);
        }
        $this->exclude($this->configuration->get('destination').'/**');
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
            
            // Contains all of the content that should be output
            $outputs = array();

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
                        $sourceFile = $this->inputFiles[$file->getPathname()] = $updatedFiles[] = new SourceFile($file);
                        $sourceFile->setHasChanged();
                    } else {
                        $sourceFile = $unchangedFiles[] = new SourceFile($file);
                    }
                } else {
                    // File is new.
                    $sourceFile = $this->inputFiles[$file->getPathname()] = $newFiles[] = new SourceFile($file);
                    $sourceFile->setHasChanged();
                }
            }

            $sourceFileSet = new SourceFileSet($newFiles, $updatedFiles, $unchangedFiles);

            if ( $sourceFileSet->hasChangedFiles() ) {
                $sourceFilesChangedEvent = new SourceFilesChangedEvent($this, $sourceFileSet);
                $this->eventDispatcher->dispatch(
                    self::EVENT_SOURCE_FILES_CHANGED,
                    new SourceFilesChangedEvent($this, $sourceFileSet)
                );

                foreach ($sourceFileSet->changedFiles() as $sourceFile) {
                    /* @var $sourceFile SourceFile */
                    if ($sourceFile->canBeProcessed()) {
                        $this->convertSourceFile($sourceFile);
                    }
                }

                $this->eventDispatcher->dispatch(
                    self::EVENT_CONVERTED,
                    new SourceFilesChangedEvent($this, $sourceFileSet)
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_BEFORE_GENERATE,
                    new SourceFilesChangedEvent($this, $sourceFileSet)
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_GENERATE,
                    new SourceFilesChangedEvent($this, $sourceFileSet)
                );
                $this->eventDispatcher->dispatch(
                    self::EVENT_AFTER_GENERATE,
                    new SourceFilesChangedEvent($this, $sourceFileSet)
                );
                
                foreach ($sourceFileSet->changedFiles() as $sourceFile) {
                    /* @var $sourceFile SourceFile */
                    if ($sourceFile->canBeProcessed()) {
                        $sourceFile->setContent($this->formatPage($sourceFile->id(), $sourceFile->content(), $sourceFile->context()));
                    }
                }

                foreach ($sourceFileSet->allFiles() as $sourceFile) {
                    /* @var $sourceFile SourceFile */
                    if ($sourceFile->isNormal() and $sourceFile->hasChanged()) {
                        $outputs[] = new SourceFileOutput($sourceFile);
                    }
                }
                
                foreach ($outputs as $output) {
                    $this->writer->write($this, $output);
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
    
    public function formatBlocks($templateId, $template, $context)
    {
        $formatContext = $this->buildFormatContext($templateId, $template, $context);
        $this->eventDispatcher->dispatch(
                self::EVENT_BEFORE_FORMAT,
                new FormatEvent($this, $formatContext)
        );
        $response = $this->formatter($formatContext->context()->get('formatter'))->formatBlocks($this, $formatContext);
        $this->eventDispatcher->dispatch(
                self::EVENT_AFTER_FORMAT,
                new FormatEvent($this, $formatContext)
        );
        return $response;
    }
    
    public function formatPage($templateId, $template, $context)
    {
        $formatContext = $this->buildFormatContext($templateId, $template, $context);
        $this->eventDispatcher->dispatch(
            self::EVENT_BEFORE_FORMAT,
            new FormatEvent($this, $formatContext)
        );
        $response = $this->formatter($formatContext->context()->get('formatter'))->formatPage($this, $formatContext);
        $this->eventDispatcher->dispatch(
            self::EVENT_AFTER_FORMAT,
            new FormatEvent($this, $formatContext)
        );
        return $response;
    }
    
    /**
     * Register a formatter
     * @param string $name
     * @param IFormatter $formatter
     */
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
    
    public function buildFormatContext($templateId, $template, $pageContext)
    {
        $context = Util::MERGE_ASSOC_ARRAY($this->buildDefaultFormatContext(), array('page' => $pageContext));
        foreach (array('layout', 'formatter', 'converters') as $key) {
            if (isset($pageContext[$key])) {
                $context[$key] = $pageContext[$key];
            }
        }
        return new FormatContext($templateId, $template, $context);
    }

    public function buildDefaultFormatContext()
    {
        $defaultContext = $this->configuration->export();
        $defaultContext['formatter'] = $this->defaultFormatter;
        $defaultContext['converters'] = array();
        return $defaultContext;
    }
    
    /**
     * Register a converter
     * @param string $name
     * @param IConverter $formatter
     */
    public function registerConverter($name, IConverter $converter)
    {
        $this->converters[$name] = $converter;
    }
    
    /**
     * Get converter
     * @param string $name
     * @return IConverter
     */
    public function converter($name)
    {
        // TODO: Throw an exception of the requested converter does not exist?
        return isset($this->converters[$name]) ? $this->converters[$name] : null;
    }
    
    public function convertSourceFile(SourceFile $sourceFile)
    {
        $converters = $sourceFile->data()->get('converters');
        if (!$converters or !is_array($converters)) { return; }
        foreach ($sourceFile->data()->get('converters') as $converter) {
            $this->eventDispatcher->dispatch(
                self::EVENT_BEFORE_CONVERT,
                new ConvertSourceFileEvent($this, $sourceFile, $converter)
            );
            $this->converter($converter)->convert($this, new SourceFileConverterContext($sourceFile));
            $this->eventDispatcher->dispatch(
                self::EVENT_AFTER_CONVERT,
                new ConvertSourceFileEvent($this, $sourceFile, $converter)
            );
        }
    }

    /**
     * Derive the formatter for a source file
     * 
     * Convenience method. Is not DRY. Similar functionality exists in
     * buildDefaultFormatContext and buildFormatContext.
     * 
     * @param SourceFile $sourceFile
     */
    public function deriveSourceFileFormatter(SourceFile $sourceFile)
    {
        if ($formatter = $sourceFile->data()->get('formatter')) {
            return $formatter;
        }
        return $this->defaultFormatter;
    }
    
}
