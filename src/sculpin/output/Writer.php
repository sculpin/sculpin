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
    /**
     * Write output file
     * @param Sculpin $sculpin Sculpin
     * @param IOutput $output Output
     */
    public function write(Sculpin $sculpin, IOutput $output)
    {
        $destination = $sculpin->configuration()->getPath('destination');
        $outputPath = $destination.'/'.$output->permalink()->relativeFilePath();
        if ($output->hasFileReference()) {
            $sculpin->filesystem()->copy($output->file(), $outputPath, true);
        } else {
            $sculpin->filesystem()->mkdir(dirname($outputPath));
            file_put_contents($outputPath, $output->content());
        }
    }
}
