<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\configuration;

use Dflydev\DotAccessConfiguration\ConfigurationFactoryInterface;

class ConfigurationFactory implements ConfigurationFactoryInterface
{
    /**
     * {@inheritdocs}
     */
    public function create()
    {
        return new Configuration;
    }
}