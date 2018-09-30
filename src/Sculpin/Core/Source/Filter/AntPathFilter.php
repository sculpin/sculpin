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
    private $antPathMatcher;
    private $patterns;
    /**
     * @var DirectorySeparatorNormalizer
     */
    private $directorySeparatorNormalizer;

    public function __construct(
        array $paths,
        AntPathMatcher $antPathMatcher = null,
        DirectorySeparatorNormalizer $directorySeparatorNormalizer = null
    ) {
        if (null === $antPathMatcher) {
            $antPathMatcher = new AntPathMatcher;
        }
        $this->patterns = array_map(function ($path) use ($antPathMatcher) {
            return $antPathMatcher->isPattern($path) ? $path : $path.'/**';
        }, $paths);
        $this->antPathMatcher = $antPathMatcher;
        $this->directorySeparatorNormalizer = $directorySeparatorNormalizer ?: new DirectorySeparatorNormalizer;
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
