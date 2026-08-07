<?php

declare(strict_types=1);

namespace Sculpin\Bundle\EditorBundle;

use League\MimeTypeDetection\MimeTypeDetector;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use Sculpin\Bundle\SculpinBundle\HttpServer\ContentFetcher;
use Sculpin\Bundle\SculpinBundle\HttpServer\HttpServer;
use Sculpin\Core\Source\SourceSet;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class InBrowserEditorContentFetcher implements ContentFetcher
{
    protected array $pathMap;
    protected array $sourceMap;
    protected string $docroot;
    protected string $sourceDir;

    public function __construct(
        SourceSet $set,
        string $docroot,
        string $sourceDir,
        protected readonly MimeTypeDetector $detector,
    ) {
        $this->docroot   = rtrim($docroot, '/') . '/';
        $this->sourceDir = rtrim($sourceDir, '/') . '/';

        $this->buildPathMap($set);
        $this->buildSourceMap();
    }

    public function handleRequest(
        string $path,
        ServerRequestInterface $request,
        OutputInterface $output,
    ): ?Response {
        $params = $request->getQueryParams();
        $url = $params['url'] ?? '';
        $source = $params['source'] ?? '';
        $requestMethod = $request->getMethod();

        return match (true) {
            str_ends_with($path, '_SCULPIN_/editor.js') => new Response(
                200,
                ['Content-Type' => 'text/javascript'],
                $this->editorJs()
            ),
            str_ends_with($path, '_SCULPIN_/editor.css') => new Response(
                200,
                ['Content-Type' => 'text/css'],
                $this->editorCss()
            ),
            str_ends_with($path, '_SCULPIN_/hash') && $requestMethod === 'GET' => $this->diskPathExists($url)
                ? new Response(200, ['Content-Type' => 'application/json'], json_encode(['hash' => $this->hash($url)]))
                : new Response(
                    400, // 400 is intentional, as the path may exist in Output yet not exist as a Source file
                    ['Content-Type' => 'application/json'],
                    json_encode(['error' => 'Not Found-ish'])
                ),
            strstr($path, '/_SCULPIN_/metadata') && $requestMethod === 'GET' => $this->getMetadataResponse($url, $source),
            str_ends_with($path, '_SCULPIN_/update') && $requestMethod === 'PUT' => $this->applyUpdate($request, $output),
            default => null,
        };
    }

    public function buildPathMap(SourceSet $set): void
    {
        $pathMap = [];
        $sources = $set->allSources();

        foreach ($sources as $source) {
            $relativePath      = ltrim($source->permalink()->relativeFilePath(), '/\\');
            $pathKey           = $relativePath;
            $pathMap[$pathKey] = $source->file()->getPathname();
        }

        $this->pathMap = $pathMap;
    }

    public function buildSourceMap(): void
    {
        $files = Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->followLinks()
            ->in($this->sourceDir);

        $this->sourceMap = [];

        foreach ($files as $file) {
            $this->sourceMap[$file->getRelativePathname()] = [
                'pathname' => $file->getRelativePathname(),
                'file' => $file->getFilename(),
                'type' => $file->getType(),
                'mime' => $this->detector->detectMimeTypeFromFile($file->getPathname()),
                'ext' => mb_strtolower($file->getExtension()),
            ];
        }
    }

    public function fetchData(string $path): ?string
    {
        $body = file_get_contents($path);
        $relativePath = str_replace($this->docroot, '', $path);

        return $body ? $this->process($relativePath, $body) : null;
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

        $metadata = $this->getMetadata($path);
        $json = json_encode($metadata, JSON_PRETTY_PRINT);

        $injectionString = <<<EOF
            <script>
              var SCULPIN_EDITOR_METADATA = {$json};
            </script>
            <script src="/_SCULPIN_/editor.js" type="text/javascript"></script>
            <link href="/_SCULPIN_/editor.css" rel="stylesheet" type="text/css" />
        EOF;

        $headPos = stripos($body, '</head>');
        if (false === $headPos) {
            $bodyPos = stripos($body, '<body');
            if ($bodyPos) {
                $body = str_ireplace('<body', PHP_EOL . '<head></head>' . PHP_EOL . '<body', $body);
                $headPos = stripos($body, '</head>');
            }
        }

        // modify the body content to activate the live editor

        // No head found, append and hope it works
        if (false === $headPos) {
            return $body . $injectionString;
        }

        // inject the live editor into the head
        return str_ireplace(
            '</head>',
            PHP_EOL . $injectionString . PHP_EOL . '</head>',
            $body,
        );
    }

    public function editorJs(): string
    {
        return file_get_contents(__DIR__ . '/Resources/js/editor.js') ?: '';
    }

    public function diskPathExists(string $path): bool
    {
        if (!isset($this->pathMap[$path])) {
            return false;
        }

        return file_exists($this->pathMap[$path]);
    }

    public function sourceExists(string $sourcePath): bool
    {
        if (!isset($this->sourceMap[$sourcePath])) {
            return false;
        }

        $fullPath = $this->sourceDir . $this->sourceMap[$sourcePath]['pathname'];

        return file_exists($fullPath);
    }

    public function save(string $sourcePath, string $content): void
    {
        if (!$this->sourceExists($sourcePath)) {
            return;
        }

        file_put_contents($this->sourceDir . $this->sourceMap[$sourcePath]['pathname'], $content);
    }

    public function hash(string $path): ?string
    {
        if (!$this->diskPathExists($path)) {
            return null;
        }

        return md5_file($this->docroot . $path) ?: null;
    }

    public function editorCss(): string
    {
        return file_get_contents(__DIR__ . '/Resources/css/editor.css') ?: '';
    }

    public function getMetadata(string $path = '', string $source = ''): array
    {
        $url = $path ? $this->pathMap[$path] ?? $source : $source;

        // Normalize $url by erasing the sourceDir, if present
        $diskPath = str_replace($this->sourceDir, '', $url);
        $fullDiskPath = $this->sourceDir . $diskPath;

        $content = file_exists($fullDiskPath) ? file_get_contents($fullDiskPath) : null;

        // @todo Calculate the rendered view path, if possible
        $renderedView = $this->docroot . $path;

        $output = [
            'url' => $path,
            'pathMap' => $this->pathMap,
            'sourceMap' => $this->sourceMap, // @todo see if this can include the generated file path
        ];

        if ($content) {
            $output['diskPath'] = $diskPath;
            $output['content'] = $content;
            $output['contentHashSource'] = md5_file($fullDiskPath);

            // renderedView does not map 1:1 to all files. For example,
            // layouts map to all files that use the layout.
            //
            // This makes it difficult to decide which file should be
            // the source of truth for whether an edit has been applied.
            //
            // Punting on this for now, and only providing generated
            // file hash for 1:1 mappings.
            $output['contentHashGenerated'] = ($path && file_exists($renderedView))
                ? md5_file($renderedView)
                : 'unknown';
        }

        return $output;
    }

    /**
     * @param string $url
     * @param string $source
     * @return Response
     */
    protected function getMetadataResponse(string $url, string $source): Response
    {
        try {
            return new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($this->getMetadata(path: $url, source: $source))
            );
        } catch (\Exception $e) {
            return new Response(
                404,
                ['Content-Type' => 'application/json'],
                json_encode(['error' => 'Not Found'])
            );
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param OutputInterface $output
     * @return Response
     */
    public function applyUpdate(ServerRequestInterface $request, OutputInterface $output): Response
    {
        $edit = json_decode($request->getBody()->getContents(), true);

        if (!$this->sourceExists($edit['diskPath'])) {
            HttpServer::logRequest($output, 404, $request);

            $notFoundMessage = '<h1>404</h1><h2>Not Found</h2>'
                . '<p>'
                . 'The embedded <a href="https://sculpin.io">Sculpin</a> web server '
                . 'could not update the requested resource.'
                . '</p>';

            return new Response(404, ['Content-Type' => 'text/html'], $notFoundMessage);
        }

        $this->save($edit['diskPath'], $edit['content']);

        HttpServer::logRequest($output, 307, $request);
        $output->writeln(sprintf('Updated: %s', $edit['diskPath']));

        return new Response(307, ['Location' => $edit['path']]);
    }
}
