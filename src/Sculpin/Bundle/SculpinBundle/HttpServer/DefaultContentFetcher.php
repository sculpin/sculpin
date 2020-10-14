<?php

namespace Sculpin\Bundle\SculpinBundle\HttpServer;

class DefaultContentFetcher implements ContentFetcher
{
    public function fetchData(string $path): ?string
    {
        return file_get_contents($path) ?: null;
    }
}
