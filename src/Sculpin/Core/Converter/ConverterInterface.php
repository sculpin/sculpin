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

namespace Sculpin\Core\Converter;

/**
 * A converter knows how to convert content of a specific format.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
interface ConverterInterface
{
    /**
     * Convert the thing specified in the context.
     *
     * Usually, this means getting the content with ConverterContextInterface::content()
     * and then updating the context with ConverterContextInterface->setContent().
     */
    public function convert(ConverterContextInterface $converterContext): void;
}
