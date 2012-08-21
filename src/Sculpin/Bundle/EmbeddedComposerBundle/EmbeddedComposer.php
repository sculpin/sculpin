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
use Composer\Factory;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Repository\ArrayRepository;
use Composer\Repository\CompositeRepository;
use Composer\Repository\FilesystemRepository;
use Seld\JsonLint\ParsingException;

/**
 * Embedded Composer.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class EmbeddedComposer
{
    protected $classLoader;
    protected $externalRootDir;
    protected $package;
    protected $hasInternalRepository = false;
    protected $composerFile;
    protected $config;
    private $composer;
    private $error;

    /**
     * Constructor.
     *
     * @param ClassLoader      $classLoader     Class loader
     * @param string           $externalRootDir External root directory
     * @param PackageInterface $package         Package
     */
    public function __construct(ClassLoader $classLoader, $externalRootDir = '.', PackageInterface $package = null)
    {
        $this->classLoader = $classLoader;
        $this->externalRootDir = $externalRootDir;
        $this->package = $package;

        $obj = new \ReflectionClass($this->classLoader);
        $this->internalVendorDir = dirname(dirname($obj->getFileName()));

        if (strpos($this->internalVendorDir, 'phar://')==0 || false===strpos($this->internalVendorDir, $externalRootDir)) {
            // If our vendor root does not contain our project root then we
            // can assume that we should enable the internally installed
            // repository.
            $this->hasInternalRepository = true;
        }

        $this->composerFile = $originalComposerFile = Factory::getComposerFile();
        if (0 !== strpos($originalComposerFile, '/')) {
            $this->composerFile = $externalRootDir.'/'.$originalComposerFile;
        }

        $this->config = Factory::createConfig();

        $file = new JsonFile($this->composerFile);

        if ($file->exists()) {
            try {
                $file->validateSchema(JsonFile::LAX_SCHEMA);
                $this->config->merge($file->read());
            } catch (ParsingException $e) {
                $this->error = $e;
            }
        } else {
            if ($originalComposerFile === 'composer.json') {
                $message = 'Composer could not find a composer.json file in '.realpath($externalRootDir);
            } else {
                $message = 'Composer could not find the config file: '.$this->composerFile;
            }
            $instructions = 'To initialize a project, please create a composer.json file as described in the http://getcomposer.org/ "Getting Started" section';

            $this->error = new \InvalidArgumentException($message.PHP_EOL.$instructions);
        }
    }

    /**
     * Class Loader
     *
     * @return ClassLoader
     */
    public function getClassLoader()
    {
        return $this->classLoader;
    }

    /**
     * External Root Directory
     *
     * @return string
     */
    public function getExternalRootDir()
    {
        return $this->externalRootDir;
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
     * Has an internal repository?
     *
     * @return bool
     */
    public function hasInternalRepository()
    {
        return $this->hasInternalRepository;
    }

    /**
     * Composer file
     *
     * @return string
     */
    public function getComposerFile()
    {
        return $this->composerFile;
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

        $internalRepositoryFile = $this->internalVendorDir.'/composer/installed.json';
        $filesystemRepository = new FilesystemRepository(new JsonFile($internalRepositoryFile));

        return new CompositeRepository(array(
            new ArrayRepository(array($this->package)),
            $filesystemRepository
        ));
    }

    /**
     * Error
     *
     * @return \Exception
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Process external autoloads.
     */
    public function processExternalAutoloads()
    {
        $rootDir = $this->externalRootDir;

        $vendorDir = $this->config->get('vendor-dir');
        if (0 !== strpos($vendorDir, '/')) {
            $vendorDir = $rootDir.'/'.$vendorDir;
        }

        if ($autoloadNamespacesFile = realpath($vendorDir.'/composer/autoload_namespaces.php')) {
            if ($this->internalVendorDir != dirname(dirname($autoloadNamespacesFile))) {
                // We have an autoload file that is *not* the same as the
                // autoload that bootstrapped this application.
                $map = require $autoloadNamespacesFile;
                foreach ($map as $namespace => $path) {
                    $this->classLoader->add($namespace, $path);
                }
            }
        }

        if ($autoloadClassmapFile = realpath($vendorDir.'/composer/autoload_classmap.php')) {
            if ($this->internalVendorDir != dirname(dirname($autoloadClassmapFile))) {
                // We have an autoload file that is *not* the same as the
                // autoload that bootstrapped this application.
                $classMap = require $autoloadClassmapFile;
                if ($classMap) {
                    $this->classLoader->addClassMap($classMap);
                }
            }
        }
    }
}
