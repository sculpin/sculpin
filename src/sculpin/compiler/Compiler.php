<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\compiler;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class Compiler {
    
    protected $projectRoot;

    public function __construct($projectRoot = null)
    {
        $this->projectRoot = $projectRoot !== null ? $projectRoot : dirname(dirname(dirname(__DIR__)));
    }

    /**
     * Compiles Sculpin into a single phar file
     * @param string $pharFile The full path to the file to create
     */
    public function compile($pharFile = 'sculpin.phar')
    {

        $process = new Process('git log --pretty="%h" -n1 HEAD');
        if ($process->run() != 0) {
            throw new \RuntimeException('Could not determine current version from git.');
        }
        $this->version = trim($process->getOutput());

        $process = new Process('git branch --contains HEAD');
        if ($process->run() == 0) {
            if (preg_match('/^\*\s+(.+?)$/', trim($process->getOutput()), $matches)) {
                $this->version = "dev-{$matches[1]}-{$this->version}";
            }
        }

        $process = new Process('git describe --exact-match HEAD');
        if ($process->run() == 0) {
            $this->version = trim($process->getOutput());
        }
        
        $alias = 'sculpin-'.$this->version.'.phar';
        
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, $alias);
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        
        $phar->startBuffering();
        
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->notName('Compiler.php')
            ->in($this->projectRoot . '/src')
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->in(array(
                $this->projectRoot . '/vendor/composer/composer/src',
                $this->projectRoot . '/vendor/dflydev/ant-path-matcher/src',
                $this->projectRoot . '/vendor/dflydev/markdown/src',
                $this->projectRoot . '/vendor/justinrainbow/json-schema/src',
                $this->projectRoot . '/vendor/seld/jsonlint/src',
                $this->projectRoot . '/vendor/symfony',
                $this->projectRoot . '/vendor/twig/twig/lib',
            ))
        ;
        
        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }
        
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/vendor/composer/composer/res/composer-schema.json'));
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/vendor/.composer/ClassLoader.php'));
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/vendor/.composer/autoload.php'));
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/vendor/.composer/autoload_namespaces.php'));
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/vendor/.composer/autoload_classmap.php'));
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/vendor/.composer/installed.json'));

        $this->addSculpinBin($phar);
        
        $phar->setStub($this->getStub($alias));
        
        $phar->stopBuffering();
        
        $phar->compressFiles(\Phar::GZ);
        
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/LICENSE'), false);
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/README.md'), false);
        
        unset($phar);
        
    }
    
    private function addFile(\Phar $phar, $file, $strip = true)
    {
        $path = str_replace($this->projectRoot.'/', '', $file->getRealPath());
        if ($strip) {
            $content = php_strip_whitespace($file);
        } else {
            $content = "\n".file_get_contents($file)."\n";
        }
        $content = str_replace('@package_version@', $this->version, $content);
        $phar->addFromString($path, $content);
    }
    
    private function addSculpinBin($phar)
    {
        $content = file_get_contents($this->projectRoot.'/bin/sculpin');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/sculpin', $content);
    }

    private function getStub($alias)
    {
        return <<<EOF
#!/usr/bin/env php
<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Phar::mapPhar('$alias');

define('SCULPIN_RUNNING_AS_PHAR', true);

require 'phar://sculpin.phar/bin/sculpin';

__HALT_COMPILER();
EOF;
    }
}
