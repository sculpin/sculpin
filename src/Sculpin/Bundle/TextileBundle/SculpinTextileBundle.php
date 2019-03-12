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

namespace Sculpin\Bundle\TextileBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class SculpinTextileBundle extends Bundle
{
    public const CONVERTER_NAME = 'textile';
}
