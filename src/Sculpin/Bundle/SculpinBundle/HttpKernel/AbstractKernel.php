<?php declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\HttpKernel;

use Sculpin\Bundle\ContentTypesBundle\SculpinContentTypesBundle;
use Sculpin\Bundle\MarkdownBundle\SculpinMarkdownBundle;
use Sculpin\Bundle\MarkdownTwigCompatBundle\SculpinMarkdownTwigCompatBundle;
use Sculpin\Bundle\PaginationBundle\SculpinPaginationBundle;
use Sculpin\Bundle\PostsBundle\SculpinPostsBundle;
use Sculpin\Bundle\SculpinBundle\SculpinBundle;
use Sculpin\Bundle\StandaloneBundle\SculpinStandaloneBundle;
use Sculpin\Bundle\TextileBundle\SculpinTextileBundle;
use Sculpin\Bundle\ThemeBundle\SculpinThemeBundle;
use Sculpin\Bundle\TwigBundle\SculpinTwigBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Base Kernel implementation
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
abstract class AbstractKernel extends Kernel
{
    protected $projectDir;
    protected $missingSculpinBundles = [];

    /**
     * {@inheritdoc}
     */
    public function __construct($environment, $debug, $projectDir = null)
    {
        if (null !== $projectDir) {
            $this->projectDir = $projectDir;
            $this->rootDir = $projectDir.'/app';
        }

        parent::__construct($environment, $debug);
    }

    /**
     * {@inheritdoc}
     */
    protected function getKernelParameters()
    {
        if (null === $this->projectDir) {
            $this->projectDir = dirname($this->rootDir);
        }

        return array_merge(parent::getKernelParameters(), [
            'sculpin.project_dir' => $this->projectDir,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = [
            new SculpinStandaloneBundle,
            new SculpinMarkdownBundle,
            new SculpinTextileBundle,
            new SculpinMarkdownTwigCompatBundle,
            new SculpinPaginationBundle,
            new SculpinBundle,
            new SculpinThemeBundle,
            new SculpinTwigBundle,
            new SculpinContentTypesBundle,
            new SculpinPostsBundle,
        ];

        foreach ($this->getAdditionalSculpinBundles() as $class) {
            if (class_exists($class)) {
                $bundles[] = new $class();
            } else {
                $this->missingSculpinBundles[] = $class;
            }
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // Load defaults.
        $loader->load(__DIR__.'/../Resources/config/kernel.yml');

        if (file_exists($file = $this->rootDir.'/config/sculpin_kernel_'.$this->getEnvironment().'.yml')) {
            $loader->load($file);
        } elseif (file_exists($file = $this->rootDir.'/config/sculpin_kernel.yml')) {
            $loader->load($file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if (true === $this->booted) {
            return;
        }

        parent::boot();

        $this->container->compile();
    }

    /**
     * {@inheritdoc}
     */
    protected function buildContainer()
    {
        $container = $this->getContainerBuilder();
        $container->addObjectResource($this);
        $this->prepareContainer($container);

        if (file_exists($this->rootDir.'/config/sculpin_services.yml')) {
            $loader = new YamlFileLoader($container, new FileLocator($this->rootDir.'/config'));
            $loader->load('sculpin_services.yml');
        }

        if (null !== $cont = $this->registerContainerConfiguration($this->getContainerLoader($container))) {
            $container->merge($cont);
        }

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeContainer()
    {
        $container = $this->buildContainer();
        $container->set('kernel', $this);
        $this->container = $container;
    }

    /**
     * Get Sculpin bundles that were requested but were not found
     *
     * This can happen if a bundle is requested but has not been required and
     * installed by Composer. Chances are this will lead to a lot of really bad
     * things. This should be checked early by any Console applications to
     * ensure that proper warnings are issued if there are any missing bundles
     * detected.
     */
    public function getMissingSculpinBundles(): array
    {
        return $this->missingSculpinBundles;
    }

    /**
     * Get additional Sculpin bundles to register
     */
    abstract protected function getAdditionalSculpinBundles(): array;
}
