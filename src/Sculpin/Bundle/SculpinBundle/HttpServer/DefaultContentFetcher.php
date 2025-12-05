<?php

declare(strict_types=1);

namespace Sculpin\Bundle\SculpinBundle\HttpServer;

use Sculpin\Core\Source\SourceSet;

class DefaultContentFetcher implements ContentFetcher
{
    public function fetchData(string $path): ?string
    {
        return file_get_contents($path) ?: null;
    }

    public function buildPathMap(SourceSet $set): void
    {
        // Nothing to do here.
    }
}
