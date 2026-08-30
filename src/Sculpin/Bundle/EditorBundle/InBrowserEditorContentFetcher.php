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

/**
 * Provides the routes, logic, and resources for the In-browser Editor interface
 */
class InBrowserEditorContentFetcher implements ContentFetcher
{
    /** @var array A list of rendered output URLs, pointing to their Source file's full path */
    protected array $pathMap;

    /** @var array A list of all source files and their info */
    protected array $sourceMap;

    /** @var string Location of the rendered output files */
    protected string $docroot;

    /** @var string Location of the user's website source files */
    protected string $sourceDir;

    public function __construct(
        SourceSet $set,
        string $docroot,
        string $sourceDir,
        protected readonly MimeTypeDetector $detector,
    ) {
        $this->docroot   = realpath($docroot) . DIRECTORY_SEPARATOR;
        $this->sourceDir = realpath($sourceDir) . DIRECTORY_SEPARATOR;

        $this->buildPathMap($set);
        $this->buildSourceMap();
    }

    /**
     * Checks incoming HTTP Requests for specific URL prefixes to activate the Editor logic
     *
     * Enables loading JS and CSS for the editor, fetching hash info and metadata for files,
     * and updating/creating files in the Source Dir.
     *
     * @param string $path
     * @param ServerRequestInterface $request
     * @param OutputInterface $output
     * @return Response|null
     * @throws \Exception
     */
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
            str_ends_with($path, '_SCULPIN_/create') && $requestMethod === 'PUT' => $this->createFile($request, $output),
            default => null,
        };
    }

    /**
     * Builds the data structure that maps generated files (URLs) to their
     * corresponding file in the Source Directory.
     *
     * For generated files, such as Tags or Categories or Pagination, multiple
     * paths may map to the same Source file - the template for that type of page.
     *
     * @param SourceSet $set
     * @return void
     */
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

        // Sort the list of source files by a specific algorithm:
        // - Leading-Underscores (_posts, _partials, _includes, etc) go last
        // - Deeper paths go second-to-last (subfolders)
        // - Otherwise, regular string comparison rules apply
        uksort($this->sourceMap, function (string $a, string $b) {
            $aStartsWithUnderscore = str_starts_with($a, '_');
            $aDepth = substr_count($a, DIRECTORY_SEPARATOR);
            $bStartsWithUnderscore = str_starts_with($b, '_');
            $bDepth = substr_count($b, DIRECTORY_SEPARATOR);

            if ($aStartsWithUnderscore && $bStartsWithUnderscore) {
                return $b <=> $a;
            } else if ($aStartsWithUnderscore) {
                return 1;
            } else if ($bStartsWithUnderscore) {
                return -1;
            }

            if (0 === ($return = $aDepth <=> $bDepth)) {
                return $return;
            }

            return $a <=> $b;
        });
    }

    /**
     * Performs the ContentFetcher "fetchData" operation, returning the
     * HTML for the content being rendered.
     *
     * @param string $path
     * @return string|null
     */
    public function fetchData(string $path): ?string
    {
        $body = file_get_contents($path);
        $relativePath = str_replace($this->docroot, '', $path);

        return $body ? $this->process($relativePath, $body) : null;
    }

    /**
     * Intercepts the content being rendered in order to update its HTML
     * to display the In-Browser Editor.
     *
     * Skips unrecognized paths, non-HTML files, etc.
     *
     * @param string $path
     * @param string $body
     * @return string
     */
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

    /**
     * Fetch the raw editor JS
     *
     * @return string
     */
    public function editorJs(): string
    {
        return file_get_contents(__DIR__ . '/Resources/js/editor.js') ?: '';
    }

    /**
     * Check if the provided path exists in the PathMap - and, if it does,
     * check if its corresponding Source file exists on disk.
     *
     * @param string $path
     * @return bool
     */
    public function diskPathExists(string $path): bool
    {
        if (!isset($this->pathMap[$path])) {
            return false;
        }

        return file_exists($this->pathMap[$path]);
    }

    /**
     * Check if the provided Source path exists in the Source Map,
     * and if it does, check if the corresponding rendered output
     * file exists under the docroot.
     *
     * @param string $sourcePath
     * @return bool
     */
    public function sourceExists(string $sourcePath): bool
    {
        if (!isset($this->sourceMap[$sourcePath])) {
            return false;
        }

        $fullPath = $this->sourceDir . $this->sourceMap[$sourcePath]['pathname'];

        return file_exists($fullPath);
    }

    /**
     * Write provided bytes to a file in the Source dir.
     *
     * @param string $sourcePath
     * @param string $content
     * @return void
     */
    public function save(string $sourcePath, string $content): void
    {
        if (!$this->sourceExists($sourcePath)) {
            return;
        }

        file_put_contents($this->sourceDir . $this->sourceMap[$sourcePath]['pathname'], $content);
    }

    /**
     * Retrieve an MD5Hash of the requested rendered output file.
     *
     * @param string $path
     * @return string|null
     */
    public function hash(string $path): ?string
    {
        if (!$this->diskPathExists($path)) {
            return null;
        }

        return md5_file($this->docroot . $path) ?: null;
    }

    /**
     * Fetch the raw editor CSS
     *
     * @return string
     */
    public function editorCss(): string
    {
        return file_get_contents(__DIR__ . '/Resources/css/editor.css') ?: '';
    }

    /**
     * Fetch metadata for the provided Path or Source value.
     *
     * Metadata includes: url, pathMap key, sourceMap key.
     *
     * If the full disk path exists, then the metadata will
     * also include: diskPath, content, contentHashSource,
     *               contentHashGenerated.
     *
     * Content Hash Generated may be "unknown" if the file
     * does not exist; but really, the whole request should
     * have skipped past that in such a scenario.
     *
     * @param string $path
     * @param string $source
     * @return array
     */
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
     * Returns a Response containing requested Metadata.
     *
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
     * Writes incoming changes to an existing file.
     *
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

    /**
     * Creates the requested file if criteria are met, such as the file not
     * already existing.
     *
     * @param ServerRequestInterface $request
     * @param OutputInterface $output
     * @return Response
     */
    public function createFile(ServerRequestInterface $request, OutputInterface $output): Response
    {
        $edit = json_decode($request->getBody()->getContents(), true);

        $newFileName = ltrim(trim($edit['fileName'] ?? ''), '/\\');
        $newFilePath = $this->sourceDir . $newFileName;

        if (file_exists($newFilePath)) {
            // error
            throw new \Exception('file path exists!');
        }

        $touchResult = touch($newFilePath);
        if (!$touchResult) {
            throw new \Exception('bad touch!');
        }

        $newFilePath = realpath($newFilePath);

        $output->writeln(
            sprintf(
                "Received NewFilePath of %s (new file name: %s, sourcedir: %s, realpath input was: %s",
                $newFilePath === false ? 'FALSE!!!' : $newFilePath,
                $newFileName,
                $this->sourceDir,
                $this->sourceDir . $newFileName,
            )
        );

        if (empty($newFileName) || !str_starts_with($newFilePath, $this->sourceDir)) {
            HttpServer::logRequest($output, 400, $request);
            $output->writeln(
                sprintf(
                    "Rejected NewFilePath of %s (new file name: %s, sourcedir: %s",
                    $newFilePath,
                    $newFileName,
                    $this->sourceDir
                )
            );

            $forbiddenMessage = '<h1>400</h1><h2>Bad Request</h2>'
                . '<p>'
                . 'The embedded <a href="https://sculpin.io">Sculpin</a> web server '
                . 'could not create the requested resource.'
                . '</p>';

            return new Response(403, ['Content-Type' => 'text/html'], $forbiddenMessage);
            // throw new \Exception("Detected a bad filename! " . $newFileName);
        }

        if ($this->sourceExists($newFileName) || filesize($newFilePath) > 0) {
            HttpServer::logRequest($output, 403, $request);

            $forbiddenMessage = '<h1>403</h1><h2>Forbidden</h2>'
                . '<p>'
                . 'The embedded <a href="https://sculpin.io">Sculpin</a> web server '
                . 'could not update the requested resource.'
                . '</p>';

            return new Response(403, ['Content-Type' => 'text/html'], $forbiddenMessage);
        }

        $defaultContent = <<<EOT
        ---
        layout: default
        ---
        ## My New Page

        Hello and welcome to a new page on my website!
        EOT;

        file_put_contents($newFilePath, $defaultContent);

        HttpServer::logRequest($output, 307, $request);
        $output->writeln(sprintf('Created: %s', $newFileName));

        // This is a least-effort guess that does not factor in Content Types/Generators/Pagination
        $redirectLocation = explode('.', $newFileName)[0];
        if (str_starts_with($redirectLocation, '_')) {
            return new Response(307, ['Location' => '/']);
        }

        return new Response(307, ['Location' => $redirectLocation]);
    }
}
