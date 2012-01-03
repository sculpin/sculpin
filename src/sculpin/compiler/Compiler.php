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

        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $process = new Process('git log --pretty="%h" -n1 HEAD');
        if ($process->run() != 0) {
            throw new \RuntimeException('The git binary cannot be found.');
        }
        $this->version = trim($process->getOutput());

        $phar = new \Phar($pharFile, 0, 'sculpin.phar');
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
                $this->projectRoot . '/vendor/symfony',
                $this->projectRoot . '/vendor/dflydev/dflydev-util-antPathMatcher/src',
                $this->projectRoot . '/vendor/dflydev/markdown/src',
            ))
        ;
        
        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }
        
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/vendor/.composer/autoload.php'));
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/vendor/.composer/autoload_namespaces.php'));
        
        $this->addSculpinBin($phar);
        
        $phar->setStub($this->getStub());
        
        $phar->stopBuffering();
        
        $phar->compressFiles(\Phar::GZ);
        
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/LICENSE'), false);
        $this->addFile($phar, new \SplFileInfo($this->projectRoot . '/README.md'), false);
        
        unset($phar);
        
    }
    
    private function addFile(\Phar $phar, $file, $strip = true)
    {
        $path = str_replace($this->projectRoot.'/', '', $file->getRealPath());
        print " [$path]\n";
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

    private function getStub()
    {
        return <<<'EOF'
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

Phar::mapPhar('sculpin.phar');

require 'phar://sculpin.phar/bin/sculpin';

__HALT_COMPILER();
EOF;
    }
}