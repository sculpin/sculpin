<?php

namespace Sculpin\Bundle\SculpinBundle\HttpServer;

use Sculpin\Core\Source\SourceSet;

class LiveEditorContentFetcher implements ContentFetcher
{
    protected $pathMap;
    protected $docroot;
    protected $sourceDir;

    public function __construct(SourceSet $set, string $docroot, string $sourceDir)
    {
        $this->docroot   = rtrim($docroot, '/') . '/';
        $this->sourceDir = rtrim($sourceDir, '/') . '/';

        $this->buildPathMap($set);
    }

    public function buildPathMap(SourceSet $set): void
    {
        $this->pathMap = [];
        $sources       = $set->allSources();

        foreach ($sources as $source) {
            if ($source->isGenerated() || $source->isGenerator()) {
                continue;
            }

            $this->pathMap[$this->docroot . $source->permalink()->relativeFilePath()] = $source->file()->getPathname();
        }
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
        ]);

        // modify the body content to activate the live editor
        return $body . <<<EOF
        <script>
          var SCULPIN_EDITOR_METADATA = $json;
        </script>
        <script src="/_SCULPIN_/editor.js" type="text/javascript"></script>
EOF;
    }

    public function editorJs()
    {
        return file_get_contents(__DIR__ . '/../Resources/js/editor.js');
    }
}
