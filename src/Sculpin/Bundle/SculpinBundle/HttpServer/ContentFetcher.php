<?php

declare(strict_types=1);

namespace Sculpin\Bundle\SculpinBundle\HttpServer;

use Sculpin\Core\Source\SourceSet;

interface ContentFetcher
{
    public function fetchData(string $path): ?string;
    public function buildPathMap(SourceSet $set): void;
}
