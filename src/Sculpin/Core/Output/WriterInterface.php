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
interface WriterInterface
{
    /**
     * Write output
     *
     * @param OutputInterface $output Output
     */
    public function write(OutputInterface $output);
}
