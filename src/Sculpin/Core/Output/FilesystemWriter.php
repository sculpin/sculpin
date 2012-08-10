<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Output;

/**
 * Writer.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class FilesystemWriter implements WriterInterface
{
    /**
     * Constructor.
     *
     * @param Filesystem $filesystem Filesystem
     * @param string     $outputDir  Output directory
     */
    public function __construct($filesystem, $outputDir)
    {
        $this->filesystem = $filesystem;
        $this->outputDir = $outputDir;
    }
    /**
     * {@inheritdoc}
     */
    public function write(OutputInterface $output)
    {
        $outputPath = $this->outputDir.'/'.$output->permalink()->relativeFilePath();
        if ($output->hasFileReference()) {
            $this->filesystem->copy($output->file(), $outputPath, true);
        } else {
            $this->filesystem->mkdir(dirname($outputPath));
            file_put_contents($outputPath, $output->content());
        }
    }
}
