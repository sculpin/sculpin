<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\EmbeddedComposerBundle;

use Composer\Autoload\ClassLoader;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Repository\ArrayRepository;
use Composer\Repository\CompositeRepository;
use Composer\Repository\FilesystemRepository;


/**
 * Embedded Composer.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class EmbeddedComposer
{
    protected $hasInternalRepository = false;

    /**
     * Constructor.
     *
     * @param ClassLoader      $classLoader Class loader
     * @param PackageInterface $package     Package
     */
    public function __construct(ClassLoader $classLoader, PackageInterface $package)
    {
        $this->classLoader = $classLoader;
        $this->package = $package;

        $obj = new \ReflectionClass($this->classLoader);
        $this->internalVendorRoot = dirname(dirname($obj->getFileName()));

        if (strpos($this->internalVendorRoot, 'phar://')==0 || false===strpos($this->internalVendorRoot, $rootDir)) {
            // If our vendor root does not contain our project root then we
            // can assume that we should enable the internally installed
            // repository.
            $this->hasInternalRepository = true;
        }
    }

    /**
     * Package
     *
     * @return PackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Process any external autoloads.
     *
     * @param string $rootDir External root directory
     */
    public function processExternalAutoloads($rootDir = null)
    {
        if (!$rootDir) {
            return;
        }

        if ($autoloadNamespacesFile = realpath($rootDir.'/vendor/composer/autoload_namespaces.php')) {
            if ($this->internalVendorRoot != dirname(dirname($autoloadNamespacesFile))) {
                // We have an autoload file that is *not* the same as the
                // autoload that bootstrapped this application.
                $map = require $autoloadNamespacesFile;
                foreach ($map as $namespace => $path) {
                    $this->classLoader->add($namespace, $path);
                }
            }
        }

        if ($autoloadClassmapFile = realpath($rootDir.'/vendor/composer/autoload_classmap.php')) {
            if ($this->internalVendorRoot != dirname(dirname($autoloadClassmapFile))) {
                // We have an autoload file that is *not* the same as the
                // autoload that bootstrapped this application.
                $classMap = require $autoloadClassmapFile;
                if ($classMap) {
                    $this->classLoader->addClassMap($classMap);
                }
            }
        }
    }

    /**
     * Has an internal repository?
     *
     * @return bool
     */
    public function hasInternalRepository()
    {
        return $this->hasInternalRepository;
    }

    /**
     * Get internal repository
     *
     * @return \Composer\Repository\RepositoryInterface;
     */
    public function getInternalRepository()
    {
        if (!$this->hasInternalRepository) {
            return null;
        }

        $internalRepositoryFile = $this->internalVendorRoot.'/composer/installed.json';
        $filesystemRepository = new FilesystemRepository(new JsonFile($internalRepositoryFile));

        return new CompositeRepository(array(
            new ArrayRepository(array($this->package)),
            $filesystemRepository
        ));
    }
}
