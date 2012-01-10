<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\converter;

use sculpin\converter\IConverterContext;

use sculpin\Sculpin;

interface IConverter {

    /**
     * Convert content
     * @param Sculpin $sculpin
     * @param IConverterContext $converterContext
     */
    public function convert(Sculpin $sculpin, IConverterContext $converterContext);

}