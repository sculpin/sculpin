<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\output;

use sculpin\Sculpin;

class Writer {
    
    public function write(Sculpin $sculpin, IOutput $output)
    {
        $destination = $sculpin->configuration()->getPath('destination');
        if ($output->canHavePermalink()) {
            $outputPath = $destination.'/'.$this->generatePermalinkPathname($sculpin, $output);
        } else {
            $outputPath = $destination.'/'.$output->pathname();
        }
        $this->recursiveMkdir(dirname($outputPath));
        if ($output->hasFileReference()) {
            copy($output->file(), $outputPath);
        } else {
            file_put_contents($outputPath, $output->content());
        }
    }
    
    protected function generatePermalinkPathname(Sculpin $sculpin, IOutput $output)
    {
        if (!$permalink = $output->permalink()) {
            $permalink = $sculpin->configuration()->get('permalink');
        }
        switch($permalink) {
            case 'none':
                return $output->pathname();
                break;
            case 'pretty':
                if ($response = $this->isDatePath($output->pathname())) {
                    return implode('/', array_merge($response, array('index.html')));
                } else {
                    return preg_replace('/(\.[^\.]+|\.[^\.]+\.[^\.]+)$/', '', $output->pathname()).'/index.html';
                }
                break;
            case 'date':
                if ($response = $this->isDatePath($output->pathname())) {
                    return implode('/', $response).'.html';
                }
                return preg_replace('/(\.[^\.]+|\.[^\.]+\.[^\.]+)$/', '', $output->pathname()).'.html';
                break;
            default:
                return $output->pathname();
                break;
        }
    }
    
    private function isDatePath($path)
    {
        if (preg_match('/(\d{4})\-(\d{2})\-(\d{2})\-(.+?)(\.[^\.]+|\.[^\.]+\.[^\.]+)$/', $path, $matches)) {
            return array($matches[1], $matches[2], $matches[3], $matches[4]);
        }
        return null;
    }
    
    private function recursiveMkdir($path)
    {
        $parent = dirname($path);
        if (!file_exists($parent)) {
            if (!$this->recursiveMkdir($parent)) {
                return false;
            }
        }
        if (!file_exists($path)) {
            return mkdir($path);
        }
        return true;
    }

}
