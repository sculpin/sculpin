<?php

declare(strict_types=1);

namespace Sculpin\Bundle\SculpinBundle\HttpServer;

interface ContentFetcher
{
    public function fetchData(string $path): ?string;
}
