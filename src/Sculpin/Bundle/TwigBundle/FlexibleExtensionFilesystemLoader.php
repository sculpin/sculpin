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

namespace Sculpin\Bundle\TwigBundle;

use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Sculpin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source as TwigSource;

/**
 * Flexible Extension Filesystem Loader.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FlexibleExtensionFilesystemLoader implements LoaderInterface, EventSubscriberInterface
{
    /**
     * Filesystem loader
     *
     * @var FilesystemLoader
     */
    protected $filesystemLoader;

    protected $cachedCacheKey = [];
    protected $cachedCacheKeyExtension = [];
    protected $cachedCacheKeyException = [];
    protected $extensions = [];

    /**
     * Constructor.
     *
     * @param string   $sourceDir
     * @param string[] $sourcePaths
     * @param string[] $paths
     * @param string[] $extensions
     */
    public function __construct(string $sourceDir, array $sourcePaths, array $paths, array $extensions)
    {
        $mappedSourcePaths = array_map(function ($path) use ($sourceDir) {
            return $sourceDir.'/'.$path;
        }, $sourcePaths);

        $allPaths = array_merge(
            array_filter($mappedSourcePaths, function ($path) {
                return file_exists($path);
            }),
            array_filter($paths, function ($path) {
                return file_exists($path);
            })
        );

        $this->filesystemLoader = new FilesystemLoader($allPaths);
        $this->extensions = array_map(function ($ext) {
            return $ext?'.'.$ext:$ext;
        }, $extensions);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name): TwigSource
    {
        $this->getCacheKey($name);

        $extension = $this->cachedCacheKeyExtension[$name];

        return $this->filesystemLoader->getSourceContext($name.$extension);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        if (isset($this->cachedCacheKey[$name])) {
            $extension = $this->cachedCacheKeyExtension[$name];

            return $this->cachedCacheKey[$name] = $this->filesystemLoader->getCacheKey($name.$extension);
        }

        if (isset($this->cachedCacheKeyException[$name])) {
            throw $this->cachedCacheKeyException[$name];
        }

        foreach ($this->extensions as $extension) {
            try {
                $this->cachedCacheKey[$name] = $this->filesystemLoader->getCacheKey($name.$extension);
                $this->cachedCacheKeyExtension[$name] = $extension;

                return $this->cachedCacheKey[$name];
            } catch (LoaderError $e) {
            }
        }

        throw $this->cachedCacheKeyException[$name] = new LoaderError(
            sprintf('Template "%s" is not defined.', $name)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        $this->getCacheKey($name);

        $extension = $this->cachedCacheKeyExtension[$name];

        return $this->filesystemLoader->isFresh($name.$extension, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name): bool
    {
        try {
            $this->getCacheKey($name);
        } catch (LoaderError $e) {
            return false;
        }

        $extension = $this->cachedCacheKeyExtension[$name];

        return $this->filesystemLoader->exists($name.$extension);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Sculpin::EVENT_BEFORE_RUN => 'beforeRun',
        ];
    }

    public function beforeRun(SourceSetEvent $sourceSetEvent): void
    {
        if ($sourceSetEvent->sourceSet()->newSources()) {
            $this->cachedCacheKey = [];
            $this->cachedCacheKeyExtension = [];
            $this->cachedCacheKeyException = [];
        }
    }
}
