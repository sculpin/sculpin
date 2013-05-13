<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Base Kernel implementation
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
abstract class AbstractKernel extends Kernel
{
    protected $isSymfonyStandard = false;
    protected $projectDir;

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

        return array_merge(parent::getKernelParameters(), array(
            'sculpin.project_dir' => $this->projectDir,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = array(
            new \Sculpin\Bundle\MarkdownBundle\SculpinMarkdownBundle,
            new \Sculpin\Bundle\TextileBundle\SculpinTextileBundle,
            new \Sculpin\Bundle\MarkdownTwigCompatBundle\SculpinMarkdownTwigCompatBundle,
            new \Sculpin\Bundle\PaginationBundle\SculpinPaginationBundle,
            new \Sculpin\Bundle\PostsBundle\SculpinPostsBundle,
            new \Sculpin\Bundle\SculpinBundle\SculpinBundle,
            new \Sculpin\Bundle\TwigBundle\SculpinTwigBundle,
        );

        if (!$this->isSymfonyStandard) {
            array_unshift($bundles, new \Sculpin\Bundle\StandaloneBundle\SculpinStandaloneBundle);
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
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
}
