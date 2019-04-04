<?php

declare(strict_types=1);

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
 * @author Beau Simensen <beau@dflydev.com>
 */
class FormatterManager
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Configuration
     */
    protected $siteConfiguration;

    /**
     * @var DataProviderManager
     */
    protected $dataProviderManager;

    /**
     * @var array
     */
    protected $formatters = [];

    /**
     * @var string
     */
    protected $defaultFormatter;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Configuration $siteConfiguration,
        DataProviderManager $dataProviderManager = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->siteConfiguration = $siteConfiguration;
        $this->dataProviderManager = $dataProviderManager;
    }

    protected function buildBaseFormatContext(array $context): Configuration
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

    public function registerFormatter(string $name, FormatterInterface $formatter): void
    {
        $this->formatters[$name] = $formatter;

        if (null === $this->defaultFormatter) {
            $this->defaultFormatter = $name;
        }
    }

    public function formatter(string $name): FormatterInterface
    {
        return $this->formatters[$name];
    }

    public function formatPage(string $templateId, string $template, array $context): string
    {
        $formatContext = $this->buildFormatContext($templateId, $template, $context);

        if (!$formatContext->formatter()) {
            return $template;
        }

        $this->eventDispatcher->dispatch(Sculpin::EVENT_BEFORE_FORMAT, new FormatEvent($formatContext));
        $response = $this->formatter($formatContext->formatter())->formatPage($formatContext);

        return $response;
    }

    public function formatSourcePage(SourceInterface $source): string
    {
        return $this->formatPage(
            $source->sourceId(),
            $source->content(),
            $source->data()->export()
        );
    }

    public function formatBlocks(string $templateId, string $template, array $context): array
    {
        $formatContext = $this->buildFormatContext($templateId, $template, $context);

        if (!$formatContext->formatter()) {
            return ['content' => $template];
        }

        $this->eventDispatcher->dispatch(Sculpin::EVENT_BEFORE_FORMAT, new FormatEvent($formatContext));
        $response = $this->formatter($formatContext->formatter())->formatBlocks($formatContext);

        return $response;
    }

    public function formatSourceBlocks(SourceInterface $source): array
    {
        return $this->formatBlocks(
            $source->sourceId(),
            $source->content(),
            $source->data()->export()
        );
    }

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
    public function setDataProviderManager(DataProviderManager $dataProviderManager = null): void
    {
        $this->dataProviderManager = $dataProviderManager;
    }
}
