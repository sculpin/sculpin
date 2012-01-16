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

use sculpin\Util;

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
                list($year, $yr, $month, $mo, $day, $dy) = explode('-', date('Y-y-m-n-d-j', $output->date()));
                $permalink = preg_replace('/:year/', $year, $permalink);
                $permalink = preg_replace('/:yr/', $yr, $permalink);
                $permalink = preg_replace('/:year/', $year, $permalink);
                $permalink = preg_replace('/:month/', $month, $permalink);
                $permalink = preg_replace('/:mo/', $mo, $permalink);
                $permalink = preg_replace('/:day/', $day, $permalink);
                $permalink = preg_replace('/:dy/', $dy, $permalink);
                $permalink = preg_replace('/:title/', $this->normalize($output->title()), $permalink);
                $filename = $output->pathname();
                if ($isDatePath = $this->isDatePath($output->pathname())) {
                    $filename = $isDatePath[3];
                }
                $permalink = preg_replace('/:filename/', $filename, $permalink);
                if (substr($permalink, -1, 1) == '/') {
                    $permalink .= 'index.html';
                }
                return $permalink;
                break;
        }
    }
    
    private function isDatePath($path)
    {
        if (preg_match('/(\d{4})[\/\-]*(\d{2})[\/\-]*(\d{2})[\/\-]*(.+?)(\.[^\.]+|\.[^\.]+\.[^\.]+)$/', $path, $matches)) {
            return array($matches[1], $matches[2], $matches[3], $matches[4]);
        }
        return null;
    }
    
    /**
     * Recursively make directories to to and including specified path
     * @param string $path
     */
    private function recursiveMkdir($path)
    {
        return Util::RECURSIVE_MKDIR($path);
    }
    
    /**
     * Normalize parameter to be used in human readable URL
     * 
     * "Inspired" by Phrozn's normalize implementation.
     * @param string $param Parameter to normalize
     * @param string $space What to use as space separator
     * @return string
     */
    private function normalize($param, $space = '-')
    {
        $param = trim($param);
        if (function_exists('iconv')) {
            $param = @iconv('utf-8', 'us-ascii//TRANSLIT', $param);
        }
        $param = preg_replace('/[^a-zA-Z0-9 -]/', '', $param);
        $param = strtolower($param);
        $param = preg_replace('/[\s-]+/', $space, $param);
        return $param;
    }

}
