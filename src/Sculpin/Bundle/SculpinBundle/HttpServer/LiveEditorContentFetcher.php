<?php

declare(strict_types=1);

namespace Sculpin\Bundle\SculpinBundle\HttpServer;

use Sculpin\Core\Source\SourceSet;

class LiveEditorContentFetcher implements ContentFetcher
{
    protected array $pathMap;
    protected string $docroot;
    protected string $sourceDir;

    public function __construct(SourceSet $set, string $docroot, string $sourceDir)
    {
        $this->docroot   = rtrim($docroot, '/') . '/';
        $this->sourceDir = rtrim($sourceDir, '/') . '/';

        $this->buildPathMap($set);
    }

    public function buildPathMap(SourceSet $set): void
    {
        $pathMap = [];
        $docRoot = rtrim($this->docroot, '/\\');
        $sources = $set->allSources();

        foreach ($sources as $source) {
            $relativePath      = ltrim($source->permalink()->relativeFilePath(), '/\\');
            $pathKey           = $docRoot . DIRECTORY_SEPARATOR . $relativePath;
            $pathMap[$pathKey] = $source->file()->getPathname();
        }

        $this->pathMap = $pathMap;
    }

    public function fetchData(string $path): ?string
    {
        $body = file_get_contents($path);

        return $body ? $this->process($path, $body) : null;
    }

    protected function process(string $path, string $body): string
    {
        // if we don't know the disk location for edits, exit early
        if (!isset($this->pathMap[$path])) {
            return $body;
        }

        // if body content doesn't end with </html>, exit early
        if (false === $htmlEndPos = stripos(substr($body, -20), '</html>')) {
            return $body;
        }

        $url      = str_replace($this->docroot, '', $path);
        $diskPath = str_replace($this->sourceDir, '', $this->pathMap[$path]);
        $content  = file_get_contents($this->pathMap[$path]);

        $json = json_encode([
            'url'      => $url,
            'diskPath' => $diskPath,
            'content'  => $content,
            'contentHash' => md5_file($path),
        ]);

        // modify the body content to activate the live editor
        return $body . <<<EOF
        <script>
          var SCULPIN_EDITOR_METADATA = {$json};
        </script>
        <script src="/_SCULPIN_/editor.js" type="text/javascript"></script>
        EOF;
    }

    public function editorJs(): string
    {
        return file_get_contents(__DIR__ . '/../Resources/js/editor.js') ?: '';
    }

    public function diskPathExists(string $path): bool
    {
        $fullPath = $this->docroot . $path;

        if (!isset($this->pathMap[$fullPath])) {
            return false;
        }

        return file_exists($this->pathMap[$fullPath]);
    }

    public function save(string $path, string $content): void
    {
        if (!$this->diskPathExists($path)) {
            return;
        }

        file_put_contents($this->pathMap[$this->docroot . $path], $content);
    }

    public function hash(string $path): ?string
    {
        if (!$this->diskPathExists($path)) {
            return null;
        }

        return md5_file($this->docroot . $path) ?: null;
    }
}
