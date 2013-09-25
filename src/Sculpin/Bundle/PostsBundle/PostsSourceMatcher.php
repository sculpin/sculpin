<?php

namespace Sculpin\Bundle\PostsBundle;

use dflydev\util\antPathMatcher\AntPathMatcher;
use Sculpin\Contrib\ProxySourceCollection\SourceMatcherInterface;
use Sculpin\Core\Source\SourceInterface;
use Sculpin\Core\Util\DirectorySeparatorNormalizer;

class PostsSourceMatcher implements SourceMatcherInterface
{
    private $antPathMatcher;
    private $patterns;
    private $publishDrafts;

    public function __construct(
        array $paths,
        $publishDrafts = false,
        AntPathMatcher $antPathMatcher = null,
        DirectorySeparatorNormalizer $directorySeparatorNormalizer = null
    ) {
        if (null === $antPathMatcher) {
            $antPathMatcher = new AntPathMatcher;
        }
        $this->patterns = array_map(function ($path) use ($antPathMatcher) {
            return $antPathMatcher->isPattern($path) ? $path : $path.'/**';
        }, $paths);
        $this->publishDrafts = $publishDrafts;
        $this->antPathMatcher = $antPathMatcher;
        $this->directorySeparatorNormalizer = $directorySeparatorNormalizer ?: new DirectorySeparatorNormalizer;
    }

    public function matchSource(SourceInterface $source)
    {
        $normalizedPath = $this->directorySeparatorNormalizer->normalize($source->relativePathname());

        foreach ($this->patterns as $pattern) {
            if ($this->antPathMatcher->match($pattern, $normalizedPath)) {
                if ($source->data()->get('draft')) {
                    if (!$this->publishDrafts) {
                        // If we are not configured to publish drafts we should
                        // inform the source that it should be skipped. This
                        // will ensure that it won't be touched by any other
                        // part of the system for this run.
                        $source->setShouldBeSkipped();

                        return false;
                    }
                }

                return true;
            }
        }

        return false;
    }
}
