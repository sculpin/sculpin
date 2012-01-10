<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\source;

use Symfony\Component\Finder\SplFileInfo;

use sculpin\configuration\YamlConfigurationBuilder;

use sculpin\configuration\Configuration;

class SourceFile {
    
    /**
     * File
     * @var \SplFileInfo
     */
    protected $file;
    
    /**
     * Content
     * @var string
     */
    protected $content;
    
    /**
     * Data
     * @var \sculpin\configuration\Configuration
     */
    protected $data;
    
    /**
     * Represents a normal file
     * 
     * Normal files are files that are not handled specially by
     * a bundle. Files that are not normal will not be formatted
     * directly.
     * 
     * @var boolean
     */
    protected $isNormal = true;
    
    /**
     * Represents a file that can be processed by Sculpin
     * @var boolean
     */
    protected $canBeProcessed;
    
    /**
     * Constructor
     */
    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
        $finfo = finfo_open(FILEINFO_MIME);
        $this->mime = finfo_file($finfo, $file);
        if (substr($this->mime, 0, 4) == 'text') {
            // Only text files can be processed by Sculpin
            $this->canBeProcessed = true;
            $content = file_get_contents($file);
            if (preg_match('/^\s*(?:---[\r\n]+|)(.+?)(?:---[\r\n]+)(.*?)$/s', $content, $matches)) {
                $this->content = $matches[2];
                if (preg_match('/^\s*[-]+\s*$/', $matches[1])) {
                    $this->data = new Configuration(array());
                } else {
                    $builder = new YamlConfigurationBuilder(array($matches[1]));
                    $this->data = $builder->build();
                }
            } else {
                $this->content = $content;
                $this->data = new Configuration(array());
            }
        } else {
            $this->content = null;
            $this->data = new Configuration(array());
            $this->canBeProcessed = false;
        }
    }
    
    public function setContent($content = null)
    {
        $this->content = $content;
    }
    
    public function content()
    {
        return $this->content;
    }
    
    /**
     * Data
     * @return \sculpin\configuration\Configuration
     */
    public function data()
    {
        return $this->data;
    }
    
    public function file()
    {
        return $this->file;
    }
    
    public function setIsNormal()
    {
        $this->isNormal = true;
    }
    
    public function setIsNotNormal()
    {
        $this->isNormal = false;
    }
    
    public function isNormal()
    {
        return $this->isNormal;
    }
    
    public function canBeProcessed()
    {
        return $this->canBeProcessed;
    }
    
    public function id()
    {
        return $this->file->getRelativePathname();
    }
    
    public function context()
    {
        return $this->data->export();
    }

}
