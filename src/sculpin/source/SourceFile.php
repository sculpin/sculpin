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

use sculpin\permalink\IPermalink;

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
     * Represetns a file that has changed
     * @var boolean
     */
    protected $hasChanged;
    
    /**
     * Stores the cached MTime
     * @var integer
     */
    protected $cachedMTime;
    
    /**
     * Permalink
     * @var IPermalink
     */
    protected $permalink;
    
    /**
     * Constructor
     * 
     * @param SplFileInfo $file
     * @param boolean $raw
     */
    public function __construct(SplFileInfo $file, $raw)
    {
        $this->file = $file;
        $finfo = finfo_open(FILEINFO_MIME);
        $this->mime = finfo_file($finfo, $file);
        if (substr($this->mime, 0, 4) == 'text' and !$raw) {
            // Only text files can be processed by Sculpin
            $this->canBeProcessed = true;
            $content = file_get_contents($file);
            if (preg_match('/^\s*(?:---[\r\n]+|)(.+?)(?:---[\r\n]+)(.*?)$/s', $content, $matches)) {
                $this->content = $matches[2];
                if (preg_match('/^(\s*[-]+\s*|\s*)$/', $matches[1])) {
                    $this->data = new Configuration(array());
                } else {
                    try {
                        $builder = new YamlConfigurationBuilder($matches[1]);
                        $this->data = $builder->build();
                    } catch (\Exception $e) {
                        $this->content = $content;
                        $this->data = new Configuration(array());
                        $this->canBeProcessed = false;
                    }
                }
            } else {
                $this->content = $content;
                $this->data = new Configuration(array());
                $this->canBeProcessed = false;
            }
        } else {
            $this->content = null;
            $this->data = new Configuration(array());
            $this->canBeProcessed = false;
        }
        if ($this->data->get('date')) {
            $this->data->set('calculatedDate', strtotime($this->data->get('date')));
        }
        $this->cachedMTime = $file->getMTime();
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
    
    /**
     * File
     * @return SplFileInfo
     */
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
    
    public function hasChanged()
    {
        return $this->hasChanged;
    }
    
    public function setHasChanged()
    {
        $this->hasChanged = true;
    }
    
    public function setHasNotChanged()
    {
        $this->hasChanged = false;
    }
    
    public function id()
    {
        return 'SourceFile:'.$this->file->getRelativePathname();
    }
    
    public function context()
    {
        return $this->data->export();
    }
    
    public function cachedMTime()
    {
        return $this->cachedMTime;
    }
    
    /**
     * Permalink
     * @param IPermalink $permalink
     */
    public function setPermalink(IPermalink $permalink)
    {
        $this->permalink = $permalink;
    }
    
    /**
     * Permalink
     * @return IPermalink
     */
    public function permalink()
    {
        return $this->permalink;
    }

}
