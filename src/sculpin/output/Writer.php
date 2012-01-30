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
        $outputPath = $destination.'/'.$output->permalink()->relativeFilePath();
        $this->recursiveMkdir(dirname($outputPath));
        if ($output->hasFileReference()) {
            copy($output->file(), $outputPath);
        } else {
            file_put_contents($outputPath, $output->content());
        }
    }

    /**
     * Recursively make directories to to and including specified path
     * @param string $path
     */
    private function recursiveMkdir($path)
    {
        return Util::RECURSIVE_MKDIR($path);
    }
}
