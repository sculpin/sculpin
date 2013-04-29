<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Converter;

/**
 * Converter Interface.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class NullConverter implements ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert(ConverterContextInterface $converterContext)
    {
    }
}
