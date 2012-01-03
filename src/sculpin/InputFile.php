<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin;

use sculpin\configuration\YamlConfigurationBuilder;

use sculpin\configuration\Configuration;

class InputFile {
    
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
     * Constructor
     */
    public function __construct(\SplFileInfo $file)
    {
        $this->file = $file;
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
    }
    
    public function setContent($content = null)
    {
        $this->content = $content;
    }
    
    public function content()
    {
        return $this->content;
    }
    
    public function data()
    {
        return $this->data;
    }
    
    public function file()
    {
        return $this->file;
    }

}
