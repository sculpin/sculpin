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

namespace Sculpin\Core\Util;

/**
 * Resolves YAML configuration files, accepting both the legacy `.yml`
 * extension and the now-standard `.yaml` extension.
 *
 * The legacy extension always wins when both files exist.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
final class YamlFileResolver
{
    /**
     * Resolve a YAML file path without its extension.
     *
     * Returns the path of the first existing file, preferring `.yml` over
     * `.yaml`, or null when neither file exists.
     */
    public static function resolve(string $pathWithoutExtension): ?string
    {
        if (file_exists($file = $pathWithoutExtension.'.yml')) {
            return $file;
        }

        if (file_exists($file = $pathWithoutExtension.'.yaml')) {
            return $file;
        }

        return null;
    }
}
