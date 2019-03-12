<?php

declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Output;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class FilesystemWriter implements WriterInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $outputDir;

    public function __construct(Filesystem $filesystem, string $outputDir)
    {
        $this->filesystem = $filesystem;
        $this->outputDir  = $outputDir;
    }

    /**
     * {@inheritdoc}
     *
     * @throws IOException
     */
    public function write(OutputInterface $output): void
    {
        $outputPath = $this->outputDir.'/'.$output->permalink()->relativeFilePath();
        if ($output->hasFileReference()) {
            $this->filesystem->copy($output->file(), $outputPath, true);
        } else {
            $this->filesystem->mkdir(dirname($outputPath));
            $this->filesystem->dumpFile($outputPath, $output->formattedContent());
        }
    }

    /**
     * Set or override output directory
     *
     * @param string    $outputDir  path to desired output directory
     */
    public function setOutputDir(string $outputDir): void
    {
        $this->outputDir = $outputDir;
    }

    /**
     * Retrieve the output directory
     *
     * @return string
     */
    public function getOutputDir(): string
    {
        return $this->outputDir;
    }
}
