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
    protected $internalVendorRoot;
    protected $isSymfonyStandard = false;

    /**
     * Set internal vendor root
     *
     * @param string $internalVendorRoot Internal vendor root
     */
    public function setInternalVendorRoot($internalVendorRoot)
    {
        $this->internalVendorRoot = $internalVendorRoot;
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
            new \Sculpin\Bundle\SculpinBundle\SculpinBundle,
            new \Sculpin\Bundle\MarkdownBundle\SculpinMarkdownBundle
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
