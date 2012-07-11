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
use sculpin\configuration\YamlFileConfigurationBuilder;
use sculpin\converter\IConverter;
use sculpin\converter\SourceConverterContext;
use sculpin\event\ConvertSourceEvent;
use sculpin\event\Event;
use sculpin\event\FormatEvent;
use sculpin\event\SourceSetEvent;
use sculpin\formatter\FormatContext;
use sculpin\formatter\IFormatter;
use sculpin\output\SourceOutput;
use sculpin\permalink\SourcePermalink;
use sculpin\source\FileSource;
use sculpin\source\ISource;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sculpin
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Sculpin extends ContainerAware
{

    const VERSION = '@package_version@';
    const EVENT_BEFORE_START = 'sculpin.core.beforeStart';
    const EVENT_AFTER_START = 'sculpin.core.afterStart';
    const EVENT_BEFORE_RUN = 'sculpin.core.beforeRun';
    const EVENT_AFTER_RUN = 'sculpin.core.afterRun';
    const EVENT_BEFORE_STOP = 'sculpin.core.beforeStop';
    const EVENT_AFTER_STOP = 'sculpin.core.afterStop';
    const EVENT_SOURCE_SET_CHANGED = 'sculpin.core.sourceSetChanged';
    const EVENT_SOURCE_SET_CHANGED_POST = 'sculpin.core.sourceSetChangedPost';
    const EVENT_SOURCE_FILES_CHANGED = 'sculpin.core.inputFilesChanged';
    const EVENT_SOURCE_FILES_CHANGED_POST = 'sculpin.core.inputFilesChangedPost';
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
     *
     * @var \sculpin\configuration\Configuration
     */
    protected $configuration;

    /**
     * Bundles (by name)
     *
     * @var array
     */
    protected $bundles = array();

    /**
     * List of exclusions
     *
     * @var array
     */
    protected $exclusions = array();

    /**
     * List of ignores
     *
     * @var array
     */
    protected $ignores = array();

    /**
     * List of raws
     *
     * @var array
     */
    protected $raws = array();

    /**
     * Registered formatters
     *
     * @var array
     */
    protected $formatters = array();

    /**
     * Configuration callbacks for formatters
     *
     * Required because configuration callbacks may be assigned before
     * a formatter is actually registered.
     *
     * @var array
     */
    protected $formatterConfigurationCallbacks = array();

    /**
     * Name of the default formatter to use.
     *
     * @var string
     */
    protected $defaultFormatter;

    /**
     * Registered converters
     *
     * @var array
     */
    protected $converters = array();

    /**
     * Callbacks providing additional data
     *
     * @var array
     */
    protected $dataProviders = array();

    /**
     * Constructor
     *
     * @param Configuration $configuration
     * @param ContainerInterface $container
     */
    public function __construct(Configuration $configuration, ContainerInterface $container = null)
    {
        // Create the Container if it was not already provided.
        if (!isset($container)) {
            $container = new ContainerBuilder();

            // Register the default services for the Container.
            $container->register('sculpin.eventdispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher');
            $container->register('sculpin.writer', 'sculpin\output\Writer');
            $container->register('sculpin.matcher', 'dflydev\util\antPathMatcher\AntPathMatcher');
            $container->register('sculpin.sourceset', 'sculpin\source\SourceSet');
            $container->register('sculpin.filesystem', 'Symfony\Component\Filesystem\Filesystem');
            $container->setParameter('sculpin.finder.class', 'Symfony\Component\Finder\Finder');
        }

        // Register the configuration and Sculpin itself to the container.
        $container->set('sculpin', $this);
        $container->set('sculpin.configuration', $configuration);
        $this->setContainer($container);

        // Initialize the Configuration.
        $this->configuration = $configuration;

        foreach (array_merge($this->configuration->get('core_exclude'), $this->configuration->get('exclude')) as $pattern) {
            $this->addExclude($pattern);
        }
        foreach ($this->configuration->get('raw') as $pattern) {
            $this->addRaw($pattern);
        }
        foreach ($this->configuration->get('ignore') as $pattern) {
            $this->addIgnore($pattern);
        }
        foreach (array_merge($this->configuration->get('core_project_ignore'), $this->configuration->get('project_ignore')) as $pattern) {
            $this->addProjectIgnore($pattern);
        }

        if ($this->sourceDirIsProjectDir()) {
            $this->addProjectIgnore($this->configuration->resolve('%output_dir%/**'));
            $this->addProjectIgnore($this->configuration->resolve('%cache_dir%/**'));
            foreach ((array) $this->configuration->get('imports') as $file) {
                $this->addProjectIgnore($file);
            }
        }
    }

    /**
     * Get list of configured bundle class names from a configuration
     *
     * @param Configuration $configuration Configuration
     *
     * @return array
     */
    public static function GET_CONFIGURED_BUNDLES(Configuration $configuration)
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
        $eventdispatcher = $this->container->get('sculpin.eventdispatcher');
        $eventdispatcher->dispatch(self::EVENT_BEFORE_START, new Event($this));

        // Add all the Bundles.
        foreach (self::GET_CONFIGURED_BUNDLES($this->configuration) as $bundleClassName) {
            $this->addBundle($bundleClassName);
        }

        // Load the configuration for each Bundle.
        foreach ($this->bundles as $bundle) {
            $defaultBundleConfiguration = $bundle->getPath() . '/Resources/config/sculpin.yml';
            if (file_exists($defaultBundleConfiguration)) {
                // If the bundle has a sculpin.yml configuration file it should be
                // read and imported into the Sculpin configuration. We do not want
                // our imported configuration to clobber the existing configuration
                // values, tho. (since user overrides will have already been read)
                $configurationBuilder = new YamlFileConfigurationBuilder(array($defaultBundleConfiguration));
                $this->configuration->import($configurationBuilder->build(), false);
            }
        }

        // Boot the bundles.
        foreach ($this->bundles as $bundle) {
            $bundle->boot();
        }
        $eventdispatcher->dispatch(self::EVENT_AFTER_START, new Event($this));
    }

    /**
     * Run
     *
     * @param bool $watch    Watch
     * @param int  $pollWait Poll wait (in seconds)
     */
    public function run($watch = false, $pollWait = 2)
    {
        $eventdispatcher = $this->container->get('sculpin.eventdispatcher');
        $eventdispatcher->dispatch(self::EVENT_BEFORE_RUN);

        // Assume we want files updated since UNIX time began.
        $sinceTime = '1970-01-01T00:00:00Z';

        $running = true;

        while ($running) {

            // Get the last reported since time.
            $sinceTimeLast = $sinceTime;

            // Do this *before* we actually look for files
            // to avoid race conditions.
            $sinceTime = date('c');

            $files = $this
                ->finder()
                ->files()
                ->ignoreVCS(true)
                ->date('>= '.$sinceTimeLast)
                ->in($this->configuration->getPath('source_dir'));

            // We regenerate the whole site if an excluded file changes.
            $excludedFilesHaveChanged = false;

            $matcher = $this->container->get('sculpin.matcher');
            $sourceset = $this->container->get('sculpin.sourceset');
            foreach ($files as $file) {
                foreach ($this->ignores as $pattern) {
                    if ($matcher->match($pattern, $file->getRelativePathname())) {
                        // Ignored files are completely ignored.
                        continue 2;
                    }
                }

                foreach ($this->exclusions as $pattern) {
                    if ($matcher->match($pattern, $file->getRelativePathname())) {
                        $excludedFilesHaveChanged = true;
                        continue 2;
                    }
                }

                $isRaw = false;
                foreach ($this->raws as $pattern) {
                    if ($matcher->match($pattern, $file->getRelativePathname())) {
                        $isRaw = true;
                        break;
                    }
                }

                $source = new FileSource($file, $isRaw, true);
                $sourceset->mergeSource($source);
            }

            if ($excludedFilesHaveChanged) {
                // If any of the exluded files have changed we should
                // mark all of the sources as having changed.
                foreach ($sourceset->allSources() as $source) {
                    /* @var $source \sculpin\source\ISource */
                    $source->setHasChanged();
                }
            }

            if ($sourceset->hasUpdatedSources()) {
                print "Detected new or updated files\n";

                $eventdispatcher->dispatch(
                    self::EVENT_SOURCE_SET_CHANGED,
                    new SourceSetEvent($this, $sourceset)
                );

                $eventdispatcher->dispatch(
                    self::EVENT_SOURCE_SET_CHANGED_POST,
                    new SourceSetEvent($this, $sourceset)
                );

                foreach ($sourceset->updatedSources() as $source) {
                    $this->setSourcePermalink($source);
                    $this->convertSource($source);
                }

                foreach ($sourceset->updatedSources() as $source) {
                    if ($source->canBeFormatted()) {
                        $source->setContent($this->formatPage(
                            $source->sourceId(),
                            $source->content(),
                            $source->data()->export()
                        ));
                    }
                }

                $writer = $this->container->get('sculpin.writer');
                foreach ($sourceset->updatedSources() as $source) {
                    $writer->write($this, new SourceOutput($source));
                    print " + {$source->sourceId()}\n";
                }
            }

            if ($watch) {
                // Temporary.
                sleep($pollWait);
                clearstatcache();
                $sourceset->reset();
            } else {
                $running = false;
            }
        }

        $eventdispatcher->dispatch(self::EVENT_AFTER_RUN);
    }

    /**
     * Stop
     */
    public function stop()
    {
        $eventdispatcher = $this->container->get('sculpin.eventdispatcher');
        $eventdispatcher->dispatch(self::EVENT_BEFORE_STOP);
        $eventdispatcher->dispatch(self::EVENT_AFTER_STOP);
    }

    /**
     * Set source permalink
     *
     * @param ISource $source Source
     */
    protected function setSourcePermalink(ISource $source)
    {
        $source->setPermalink(new SourcePermalink($this, $source));
    }

    /**
     * Convert a source
     *
     * @param ISource $source Source
     */
    protected function convertSource(ISource $source)
    {
        $eventdispatcher = $this->container->get('sculpin.eventdispatcher');
        // TODO: Make 'converters' a const
        $converters = $source->data()->get('converters');

        if (!$converters || !is_array($converters)) {
            return;
        }

        foreach ($converters as $converter) {
            $eventdispatcher->dispatch(
                self::EVENT_BEFORE_CONVERT,
                new ConvertSourceEvent($this, $source, $converter)
            );

            $this->converter($converter)->convert($this, new SourceConverterContext($source));

            $eventdispatcher->dispatch(
                self::EVENT_AFTER_CONVERT,
                new ConvertSourceEvent($this, $source, $converter)
            );
        }
    }

    /**
     * Derive the formatter for a source
     *
     * Convenience method. Is not DRY. Similar functionality exists in
     * buildDefaultFormatContext and buildFormatContext.
     *
     * @param Source $source
     *
     * @return string
     */
    public function deriveSourceFormatter(ISource $source)
    {
        if ($formatter = $source->data()->get('formatter')) {
            return $formatter;
        }

        return $this->defaultFormatter;
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
     *
     * @return \dflydev\util\antPathMatcher\IAntPathMatcher
     */
    public function matcher()
    {
        return $this->container->get('sculpin.matcher');
    }

    /**
     * Add a bundle
     *
     * @param string $bundleClassName Bundle class name
     */
    protected function addBundle($bundleClassName)
    {
        if (!preg_match('/(\w+?)(|Bundle)$/', $bundleClassName, $matches)) {
            throw new \RuntimeException("Could not determine bundle name for class '$bundleClassName'");
        }
        // Create the Bundle and set up the Container for it.
        $bundle = new $bundleClassName();
        $bundle->setContainer($this->container);

        // Allow the Bundle to build itself.
        $bundle->build($this->container);

        // Add the subscriber events if it allows such a thing.
        $bundle_reflection = new \ReflectionClass($bundle);
        if ($bundle_reflection->implementsinterface('Symfony\Component\EventDispatcher\EventSubscriberInterface')) {
            $eventdispatcher = $this->container->get('sculpin.eventdispatcher');
            $eventdispatcher->addSubscriber($bundle);
        }

        // Store the Bundle for later use.
        $this->bundles[$matches[1]] = $bundle;
    }

    /**
     * Add an exclude pattern
     *
     * @param string $pattern
     */
    public function addExclude($pattern)
    {
        if (substr($pattern, 0, 2)=='./') {
            $pattern = substr($pattern, 2);
        }

        if (!in_array($pattern, $this->exclusions)) {
            $this->exclusions[] = $pattern;
        }
    }

    /**
     * Add an ignore pattern
     *
     * @param string $pattern
     */
    public function addIgnore($pattern)
    {
        if (substr($pattern, 0, 2)=='./') {
            $pattern = substr($pattern, 2);
        }

        if (!in_array($pattern, $this->ignores)) {
            $this->ignores[] = $pattern;
        }
    }

    /**
     * Add a raw pattern
     *
     * @param string $pattern
     */
    public function addRaw($pattern)
    {
        if (substr($pattern, 0, 2)=='./') {
            $pattern = substr($pattern, 2);
        }
        if (!in_array($pattern, $this->raws)) {
            $this->raws[] = $pattern;
        }
    }

    /**
     * Add a project ignore pattern
     *
     * @param string $pattern
     */
    public function addProjectIgnore($pattern)
    {
        if ($this->sourceDirIsProjectDir()) {
            $this->addIgnore($pattern);
        }
    }

    /**
     * Format blocks
     *
     * @param string $templateId Template ID
     * @param string $template   Template
     * @param array  $context    Context
     *
     * @return string
     */
    public function formatBlocks($templateId, $template, $context)
    {
        $eventdispatcher = $this->container->get('sculpin.eventdispatcher');
        $formatContext = $this->buildFormatContext($templateId, $template, $context);

        $eventdispatcher->dispatch(
            self::EVENT_BEFORE_FORMAT,
            new FormatEvent($this, $formatContext)
        );

        $response = $this->formatter($formatContext->context()->get('formatter'))->formatBlocks($this, $formatContext);

        $eventdispatcher->dispatch(
            self::EVENT_AFTER_FORMAT,
            new FormatEvent($this, $formatContext)
        );

        return $response;
    }

    /**
     * Format a page
     *
     * @param string $templateId Template ID
     * @param string $template   Template
     * @param array  $context    Context
     *
     * @return string
     */
    public function formatPage($templateId, $template, $context)
    {
        $eventdispatcher = $this->container->get('sculpin.eventdispatcher');
        $formatContext = $this->buildFormatContext($templateId, $template, $context);

        $eventdispatcher->dispatch(
            self::EVENT_BEFORE_FORMAT,
            new FormatEvent($this, $formatContext)
        );

        $response = $this->formatter($formatContext->context()->get('formatter'))->formatPage($this, $formatContext);

        $eventdispatcher->dispatch(
            self::EVENT_AFTER_FORMAT,
            new FormatEvent($this, $formatContext)
        );

        return $response;
    }

    /**
     * Register a formatter
     *
     * @param string     $name      Name of formatter
     * @param IFormatter $formatter Formatter
     */
    public function registerFormatter($name, IFormatter $formatter)
    {
        $this->formatters[$name] = $formatter;
        if (!$this->defaultFormatter) {
            $this->defaultFormatter = $name;
        }

        $this->triggerFormatterConfiguration($name);
    }

    /**
     * Register a formatter configuration callback
     *
     * Callback will be called either when the formatter is registered or
     * immediately if the formatter has already been registered.
     *
     * @param string   $name     Name of formatter
     * @param callable $callback Callback
     */
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

    /**
     * Trigger formatter configuration
     *
     * Called by both {@link registerFormatter()} and {@link registerFormatterConfigurationCallback.
     *
     * @param string $name Name of formatter
     */
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
     *
     * @param string $name Name of formatter
     *
     * @return IFormatter
     */
    public function formatter($name)
    {
        // TODO: Throw an exception of the requested formatter does not exist?
        return isset($this->formatters[$name]) ? $this->formatters[$name] : null;
    }

    /**
     * Build a Format Context instance
     *
     * @param string $templateId Template ID
     * @param string $template   Template
     * @param array  $context    Context
     *
     * @return FormatContext
     */
    protected function buildFormatContext($templateId, $template, $context)
    {
        $formatContext = $this->buildDefaultFormatContext($context);
        foreach (array('layout', 'formatter', 'converters') as $key) {
            if (isset($context[$key])) {
                $formatContext->set($key, $context[$key]);
            }
        }

        return new FormatContext($templateId, $template, $formatContext->export());
    }

    /**
     * Build default Format Context
     *
     * @param array $context Context
     *
     * @return array
     */
    protected function buildDefaultFormatContext(array $context)
    {
        $defaultContext = new Configuration(array(
            'site' => $this->configuration->export(),
            'page' => $context,
            'formatter' => $this->defaultFormatter,
            'converters' => array(),
        ));

        foreach ($this->dataProviders() as $dataProvider) {
            if (isset($context['use']) and in_array($dataProvider, $context['use'])) {
                $defaultContext->set('data.'.$dataProvider, $this->dataProvider($dataProvider));
            }
        }

        return $defaultContext;
    }

    /**
     * Register a converter
     *
     * @param string     $name      Converter name
     * @param IConverter $converter Converter
     */
    public function registerConverter($name, IConverter $converter)
    {
        $this->converters[$name] = $converter;
    }

    /**
     * Get converter
     *
     * @param string $name Converter name
     *
     * @return IConverter
     */
    public function converter($name)
    {
        // TODO: Throw an exception of the requested converter does not exist?
        return isset($this->converters[$name]) ? $this->converters[$name] : null;
    }

    /**
     * Register a data provider
     *
     * @param string   $name     Data provider name
     * @param callable $callback Date provider factory
     */
    public function registerDataProvider($name, $callback)
    {
        $this->dataProviders[$name] = $callback;
    }

    /**
     * List of all named data providers
     *
     * @return array
     */
    public function dataProviders()
    {
        return array_keys($this->dataProviders);
    }

    /**
     * Get a data provider
     *
     * @param string $name Data provider name
     *
     * @return mixed
     */
    public function dataProvider($name)
    {
        return call_user_func($this->dataProviders[$name], $this);
    }

    /**
     * Finder
     *
     * @return \Symfony\Component\Finder\Finder
     */
    public function finder()
    {
        // Retrieve which Finder we should use with Sculpin.
        $finder_class = $this->container->getParameter('sculpin.finder.class');

        // Create the new Finder object.
        return new $finder_class();
    }

    /**
     * Filesystem
     *
     * @return \Symfony\Component\Filesystem\Filesystem
     */
    public function filesystem()
    {
        return $this->container->get('sculpin.filesystem');
    }

    /**
     * Path to where cache should be stored
     *
     * @return string
     */
    protected function cachePath()
    {
        return $this->configuration->getPath('cache_dir');
    }

    /**
     * Path to where cache should be stored for a specificy directory
     *
     * @param string $directory Directory
     *
     * @return string
     */
    protected function cachePathFor($directory)
    {
        return $this->cachePath().'/'.$directory;
    }

    /**
     * Prepare cache for directory
     *
     * @param string $directory Directory
     *
     * @return string
     */
    public function prepareCacheFor($directory)
    {
        if (!$directory) {
            throw new \InvalidArgumentException("No cache directory specified");
        }

        $cacheDirectory = $this->cachePathFor($directory);
        $this->filesystem()->mkdir($cacheDirectory);

        return $cacheDirectory;
    }

    /**
     * Clear cache for directory
     *
     * @param string $directory Directory
     */
    public function clearCacheFor($directory)
    {
        if (!$directory) {
            throw new \InvalidArgumentException("No cache directory specified");
        }

        $cacheDirectory = $this->cachePathFor($directory);
        $this->filesystem()->remove(new \FilesystemIterator($cacheDirectory));
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        $this->filesystem()->remove(new \FilesystemIterator($this->cachePath()));
    }

    /**
     * Is the source directory the project directory?
     *
     * Useful for determining whether or not certain files should be
     * excluded from the file scanner. For example, if the source
     * is not the project root, likely nothing needs to be excluded. :)
     *
     * @return bool
     */
    public function sourceDirIsProjectDir()
    {
        return $this->configuration->get('source_dir_is_project_dir');
    }
}
