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

namespace Sculpin\Core\DataProvider;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class DataProviderManager
{
    /**
     * Data providers
     *
     * @var array
     */
    protected $dataProviders = [];

    public function registerDataProvider(string $name, DataProviderInterface $dataProvider): void
    {
        $this->dataProviders[$name] = $dataProvider;
    }

    /**
     * List of registered data provider names.
     *
     * @return string[]
     */
    public function dataProviders(): array
    {
        return array_keys($this->dataProviders);
    }

    /**
     * Get a data provider by name.
     *
     * @throws \InvalidArgumentException
     */
    public function dataProvider(string $name): DataProviderInterface
    {
        if (isset($this->dataProviders[$name])) {
            return $this->dataProviders[$name];
        }
        throw new \InvalidArgumentException(sprintf(
            "Requested data provider '%s' could not be found; does the content type exist, or provider not specified?",
            $name
        ));
    }
}
