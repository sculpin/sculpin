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

use Sculpin\Bundle\ComposerBundle\HttpKernel\ComposerAwareKernelInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Base Kernel implementation
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
abstract class AbstractKernel extends Kernel implements ComposerAwareKernelInterface
{
    protected $internalVendorRoot;
    protected $isSymfonyStandard = false;

    /**
     * {@inheritdoc}
     */
    public function setInternalVendorRoot($internalVendorRoot)
    {
        $this->internalVendorRoot = $internalVendorRoot;
    }

    /**
     * {@inheritdoc}
     */
    public function getInternalVendorRoot()
    {
        return $this->internalVendorRoot;
    }

    /**
     * {@inheritdoc}
     */
    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();

        $parameters['kernel.internal_vendor_root'] = $this->internalVendorRoot;

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = array(
            new \Sculpin\Bundle\ComposerBundle\SculpinComposerBundle,
            new \Sculpin\Bundle\MarkdownBundle\SculpinMarkdownBundle,
            new \Sculpin\Bundle\MarkdownTwigCompatBundle\SculpinMarkdownTwigCompatBundle,
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
        if (file_exists($file = $this->rootDir.'/config/sculpin_'.$this->getEnvironment().'.yml')) {
            $loader->load($file);
        }
    }
}
