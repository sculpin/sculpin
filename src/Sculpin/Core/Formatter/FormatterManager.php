<?php declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Formatter;

use Dflydev\DotAccessConfiguration\Configuration;
use Sculpin\Core\DataProvider\DataProviderManager;
use Sculpin\Core\Event\FormatEvent;
use Sculpin\Core\Sculpin;
use Sculpin\Core\Source\SourceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Formatter Manager.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FormatterManager
{
    /**
     * Event Dispatcher
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Site Configuration
     *
     * @var Configuration
     */
    protected $siteConfiguration;

    /**
     * Data Provider Manager
     *
     * @var DataProviderManager
     */
    protected $dataProviderManager;

    /**
     * Formatters
     *
     * @var array
     */
    protected $formatters = [];

    /**
     * Default formatter
     *
     * @var string
     */
    protected $defaultFormatter;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher     Event Dispatcher
     * @param Configuration            $siteConfiguration   Site Configuration
     * @param DataProviderManager      $dataProviderManager Data Provider Manager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Configuration $siteConfiguration,
        DataProviderManager $dataProviderManager = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->siteConfiguration = $siteConfiguration;
        $this->dataProviderManager = $dataProviderManager;
    }

    /**
     * Build base format context
     *
     * @param mixed $context
     *
     * @return Configuration
     */
    protected function buildBaseFormatContext($context): Configuration
    {
        $baseContext = new Configuration([
            'site' => $this->siteConfiguration->export(),
            'page' => $context,
            'formatter' => $this->defaultFormatter,
            'converters' => [],
        ]);

        if (isset($context['url'])) {
            if ('/' === $context['url']) {
                $relativeUrl = '.';
            } else {
                $relativeUrl = rtrim(str_repeat('../', substr_count($context['url'], '/')), '/');
            }

            $baseContext->set('relative_root_url', $relativeUrl);
        }

        foreach ($this->dataProviderManager->dataProviders() as $name) {
            if (isset($context['use']) && in_array($name, $context['use'])) {
                $baseContext->set('data.'.$name, $this->dataProviderManager->dataProvider($name)->provideData());
            }
        }

        return $baseContext;
    }

    /**
     * Build a Format Context
     *
     * @param string $templateId Template ID
     * @param string $template   Template
     * @param array  $context    Context
     *
     * @return FormatContext
     */
    public function buildFormatContext(string $templateId, string $template, array $context): FormatContext
    {
        $baseContext = $this->buildBaseFormatContext($context);

        foreach (['layout', 'formatter', 'converters'] as $key) {
            if (isset($context[$key])) {
                $baseContext->set($key, $context[$key]);
            }
        }

        return new FormatContext($templateId, $template, $baseContext->export());
    }

    /**
     * Register formatter
     *
     * @param string             $name      Name
     * @param FormatterInterface $formatter Formatter
     */
    public function registerFormatter(string $name, FormatterInterface $formatter)
    {
        $this->formatters[$name] = $formatter;

        if (null === $this->defaultFormatter) {
            $this->defaultFormatter = $name;
        }
    }

    /**
     * Formatter
     *
     * @param string $name Name
     *
     * @return FormatterInterface
     */
    public function formatter(string $name): FormatterInterface
    {
        return $this->formatters[$name];
    }

    /**
     * Format a page
     *
     * @param string $templateId Template ID
     * @param string $template   Template
     * @param array  $context    Context
     *
     * @return string|null
     */
    public function formatPage(string $templateId, string $template, array $context)
    {
        $formatContext = $this->buildFormatContext($templateId, $template, $context);

        if (!$formatContext->formatter()) {
            return $template;
        }

        $this->eventDispatcher->dispatch(Sculpin::EVENT_BEFORE_FORMAT, new FormatEvent($formatContext));
        $response = $this->formatter($formatContext->formatter())->formatPage($formatContext);

        return $response;
    }

    /**
     * Format a page for a Source
     *
     * @param SourceInterface $source Source
     *
     * @return string|null
     */
    public function formatSourcePage(SourceInterface $source)
    {
        return $this->formatPage(
            $source->sourceId(),
            $source->content(),
            $source->data()->export()
        );
    }

    /**
     * Format blocks
     *
     * @param string $templateId Template ID
     * @param string $template   Template
     * @param array  $context    Context
     *
     * @return array|null
     */
    public function formatBlocks(string $templateId, string $template, array $context)
    {
        $formatContext = $this->buildFormatContext($templateId, $template, $context);

        if (!$formatContext->formatter()) {
            return ['content' => $template];
        }

        $this->eventDispatcher->dispatch(Sculpin::EVENT_BEFORE_FORMAT, new FormatEvent($formatContext));
        $response = $this->formatter($formatContext->formatter())->formatBlocks($formatContext);

        return $response;
    }

    /**
     * Format blocks for a Source
     *
     * @param SourceInterface $source Source
     *
     * @return array|null
     */
    public function formatSourceBlocks(SourceInterface $source)
    {
        return $this->formatBlocks(
            $source->sourceId(),
            $source->content(),
            $source->data()->export()
        );
    }

    /**
     * Default Formatter.
     *
     * @return string
     */
    public function defaultFormatter(): string
    {
        return $this->defaultFormatter;
    }

    /**
     * Set Data Provider Manager.
     *
     * NOTE: This is a hack because Symfony DiC cannot handle passing Data Provider
     * Manager via constructor injection as some data providers might also rely
     * on formatter. Hurray for circular dependencies. :(
     *
     * @param DataProviderManager $dataProviderManager Data Provider Manager
     */
    public function setDataProviderManager(DataProviderManager $dataProviderManager = null)
    {
        $this->dataProviderManager = $dataProviderManager;
    }
}
