<?php

namespace Sculpin\Bundle\SculpinBundle\HttpServer;

interface ContentFetcher
{
    public function fetchData(string $path): ?string;
}
