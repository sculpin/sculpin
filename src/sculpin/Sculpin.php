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

use sculpin\permalink\SourceFilePermalink;

use sculpin\configuration\Configuration;
use sculpin\converter\IConverter;
use sculpin\converter\SourceFileConverterContext;
use sculpin\event\ConvertSourceFileEvent;
use sculpin\event\Event;
use sculpin\event\SourceFilesChangedEvent;
use sculpin\event\FormatEvent;
use sculpin\formatter\IFormatter;
use sculpin\formatter\FormatContext;
use sculpin\output\IOutput;
use sculpin\output\Writer;
use sculpin\output\SourceFileOutput;
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
    const EVENT_SOURCE_FILES_CHANGED = 'sculpin.core.inputFilesChanged';
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
     * Finder Generator
     * @var \Callable
     */
    protected $finderGenerator;
    
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
     * Configuration callbacks for formatters
     * 
     * Required because configuration callbacks may be assigned before
     * a formatter is actually registered.
     * @var array
     */
    protected $formatterConfigurationCallbacks = array();

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
     * Callbacks providing additional data
     * @var array
     */
    protected $dataProviders = array();

    /**
     * Constructor
     * @param Configuration $configuration
     * @param EventDispatcher $eventDispatcher
     * @param Callable $finderGenerator
     * @param IAntPathMatcher $matcher
     */
    public function __construct(Configuration $configuration, EventDispatcher $eventDispatcher = null, $finderGenerator = null, IAntPathMatcher $matcher = null, Writer $writer = null)
    {
        $this->configuration = $configuration;
        $this->eventDispatcher = $eventDispatcher !== null ? $eventDispatcher : new EventDispatcher();
        $this->finderGenerator = $finderGenerator !== null ? $finderGenerator : function(Sculpin $sculpin) { return new Finder(); };
        $this->matcher = $matcher !== null ? $matcher : new AntPathMatcher();
        $this->writer = $writer !== null ? $writer : new Writer();
        foreach (array_merge($this->configuration->get('core_exclude'), $this->configuration->get('exclude')) as $pattern) {
            $this->exclude($pattern);
        }
        if ($this->sourceIsProjectRoot()) {
            $this->exclude('sculpin.yml*');
            $this->exclude($this->configuration->get('destination').'/**');
            $this->exclude($this->configuration->get('cache').'/**');
        }
    }
    
    /**
     * Get list of configured bundle class names from a configuration
     * @param Configuration $configuration
     */
    static public function GET_CONFIGURED_BUNDLES(Configuration $configuration)
    {
        $configuredBundles = array();
        foreach ($configuration->get('core_bundles') as $bundleClassName) {
            if (!in_array($bundleClassName, $configuration->get('disabled_core_bundles'))) {
                // Add core bundles if they are not disabled.
                $configuredBundles[] = $bundleClassName;
            }
        }
        foreach ($configuration->get('bundles') as $bundleClassName) {
            // Add 3rd party bundles.
            $configuredBundles[] = $bundleClassName;
        }
        return $configuredBundles;
    }
    
    /**
     * Starts up Sculpin
     * 
     * This process is called to initialize plugins
     */
    public function start()
    {
        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_START, new Event($this));
        foreach (self::GET_CONFIGURED_BUNDLES($this->configuration) as $bundleClassName) {
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
            $allFiles = $this->finder()->files()->ignoreVCS(true)->in($this->configuration->getPath('source'));
            
            // We regenerate the whole site if an excluded file changes.
            // TODO: Does this make sense?
            $excludedFilesHaveChanged = false;

            foreach ( $allFiles as $file ) {
                if ($this->matcher->match('**/.*.swp', $file->getRelativePathname())) { continue; }
                foreach ($this->exclusions as $pattern) {
                    if ($this->matcher->match($pattern, $file->getRelativePathname())) {
                        if ((!isset($this->excludedFiles[$file->getPathname()])) or $file->getMTime()>$this->excludedFiles[$file->getPathname()]) {
                            $this->excludedFiles[$file->getPathname()] = $file->getMTime();
                            $excludedFilesHaveChanged = true;
                        }
                        continue 2;
                    }
                }
                $files[] = $file;
            }
            
            $newFiles = array();
            $updatedFiles = array();
            $unchangedFiles = array();
            foreach ($files as $file) {
                /* @var $file \Symfony\Component\Finder\SplFileInfo */
                if (isset($this->inputFiles[$file->getPathname()])) {
                    // File existed before.
                    if ($file->getMTime()>$this->inputFiles[$file->getPathname()]->cachedMTime()) {
                        // TODO: Maybe also check sum?
                        $sourceFile = $this->inputFiles[$file->getPathname()] = $updatedFiles[] = new SourceFile($file);
                        $sourceFile->setHasChanged();
                    } else {
                        $sourceFile = new SourceFile($file);
                        if ($excludedFilesHaveChanged and $sourceFile->canBeProcessed()) {
                            // If excluded files have changed, we want to treat
                            // any file that can be processed as changed/updated.
                            $updatedFiles[] = $sourceFile;
                            $sourceFile->setHasChanged();
                        } else {
                            $unchangedFiles[] = $sourceFile;
                        }
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

                foreach ($sourceFileSet->allFiles() as $sourceFile) {
                    /* @var $sourceFile SourceFile */
                    $sourceFile->setPermalink(new SourceFilePermalink($this, $sourceFile));
                }

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
                
                if (count($outputs)) {
                    print "Detected new or updated files\n";
                    foreach ($outputs as $output) {
                        print ' + '.$output->outputId();
                        $this->writer->write($this, $output);
                        print " [done]\n";
                    }
                    foreach ($this->formatters as $name => $formatter) {
                        /* @var $formatter \sculpin\formatter\IFormatter */
                        $formatter->resetFormatter();
                    }
                }

            }
            
            if ($watch) {
                // Temporary.
                sleep(2);
                clearstatcache();
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
        if (substr($pattern, 0, 2)=='./') {
            $pattern = substr($pattern, 2);
        }
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
        $this->triggerFormatterConfiguration($name);
    }
    
    public function registerFormatterConfigurationCallback($name, $callback)
    {
        if (!isset($this->formatterConfigurationCallbacks[$name])) {
            $this->formatterConfigurationCallbacks[$name] = array();
        }
        $this->formatterConfigurationCallbacks[$name][] = $callback;
        if ($formatter = $this->formatter($name)) {
            $this->triggerFormatterConfiguration($name);
        }
    }
    
    protected function triggerFormatterConfiguration($name)
    {
        if (isset($this->formatterConfigurationCallbacks[$name])) {
            foreach ($this->formatterConfigurationCallbacks[$name] as $callback) {
                call_user_func($callback, $this, $this->formatter($name));
            }
            // Clear the array so that future calls to this method will not run
            // these callbacks again.
            $this->formatterConfigurationCallbacks[$name] = array();
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
        $context = $this->buildDefaultFormatContext($pageContext);
        foreach (array('layout', 'formatter', 'converters') as $key) {
            if (isset($pageContext[$key])) {
                $context->set($key, $pageContext[$key]);
            }
        }
        return new FormatContext($templateId, $template, $context->export());
    }

    public function buildDefaultFormatContext(array $pageContext)
    {
        $defaultContext = new Configuration(array(
            'site' => $this->configuration->export(),
            'page' => $pageContext,
            'formatter' => $this->defaultFormatter,
            'converters' => array(),
        ));
        foreach ($this->dataProviders() as $dataProvider) {
            $defaultContext->set('data.'.$dataProvider, $this->dataProvider($dataProvider));
        }
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
     * Register a provider of data
     * @param string $name
     * @param Callable $callback
     */
    public function registerDataProvider($name, $callback)
    {
        $this->dataProviders[$name] = $callback;
    }
    
    /**
     * List of all named data providers
     * @return array
     */
    public function dataProviders() {
        return array_keys($this->dataProviders);
    }
    
    /**
     * Get a data provider
     * @param string $name
     * @return mixed
     */
    public function dataProvider($name) {
        return call_user_func($this->dataProviders[$name], $this);
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

    /**
     * Finder
     * @return \Symfony\Component\Finder\Finder
     */
    public function finder()
    {
        return call_user_func($this->finderGenerator, $this);
    }

    /**
     * Path to where cache should be stored
     * @return string
     */
    protected function cachePath()
    {
        return $this->configuration->getPath('cache');
    }

    /**
     * Path to where cache should be stored for a specificy directory
     * @return string
     */
    protected function cachePathFor($directory)
    {
        return $this->cachePath().'/'.$directory;
    }

    /**
     * Prepare cache for directory
     * @return string
     */
    public function prepareCacheFor($directory)
    {
        $cacheDirectory = $this->cachePathFor($directory);
        Util::RECURSIVE_MKDIR($cacheDirectory);
        return $cacheDirectory;
    }

    /**
     * Clear cache for directory
     */
    public function clearCacheFor($directory)
    {
        $cacheDirectory = $this->cachePathFor($directory);
        Util::RECURSIVE_UNLINK($cacheDirectory, true);
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        Util::RECURSIVE_UNLINK($this->cachePath(), true);
    }

    /**
     * Is the source folder the project root?
     * 
     * Useful for determining whether or not certain files should be
     * excluded from the file scanner. For example, if the source
     * is not the project root, likely nothing needs to be excluded. :)
     * @return boolean
     */
    public function sourceIsProjectRoot()
    {
        return $this->configuration->get('source_is_project_root');
    }
    
}
