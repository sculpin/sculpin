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

namespace Sculpin\Core\Source\Filter;

use dflydev\util\antPathMatcher\AntPathMatcher;
use Sculpin\Core\Source\SourceInterface;
use Sculpin\Core\Util\DirectorySeparatorNormalizer;

class AntPathFilter implements FilterInterface
{
    private array $patterns;

    public function __construct(
        array $paths,
        private ?AntPathMatcher $antPathMatcher = null,
        private ?DirectorySeparatorNormalizer $directorySeparatorNormalizer = null
    ) {
        $this->antPathMatcher ??= new AntPathMatcher;

        $this->patterns = array_map(
            fn($path) => $antPathMatcher->isPattern($path) ? $path : $path . '/**',
            $paths
        );

        $this->directorySeparatorNormalizer ??= new DirectorySeparatorNormalizer;
    }

    public function match(SourceInterface $source): bool
    {
        $normalizedPath = $this->directorySeparatorNormalizer->normalize($source->relativePathname());

        foreach ($this->patterns as $pattern) {
            if ($this->antPathMatcher->match($pattern, $normalizedPath)) {
                return true;
            }
        }

        return false;
    }
}
