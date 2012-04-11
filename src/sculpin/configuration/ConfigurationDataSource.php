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

use Dflydev\PlaceholderResolver\DataSource\DataSourceInterface;

class ConfigurationDataSource implements DataSourceInterface
{
    /**
     * Constructor
     * 
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key, $system = false)
    {
        if ($system) {
            return false;
        }

        return null !== $this->configuration->getRaw($key);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $system = false)
    {
        if ($system) {
            return false;
        }

        return $this->configuration->getRaw($key);
    }
}