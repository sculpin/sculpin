<?php

declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\HttpServer;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\Message\Response;
use React\Http\HttpServer as ReactHttpServer;
use React\Socket\SocketServer as ReactSocketServer;
use Sculpin\Bundle\EditorBundle\InBrowserEditorContentFetcher;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mime\MimeTypes;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class HttpServer
{
    public const int DEFAULT_PORT = 8000;

    public function __construct(
        private readonly OutputInterface $output,
        private readonly ContentFetcher $fetcher,
        string $docroot,
        private readonly string $env,
        private readonly bool $debug,
        private int $port = self::DEFAULT_PORT
    ) {
        $socketServer = new ReactSocketServer(
            sprintf('0.0.0.0:%d', $this->port),
        );

        $httpServer = $this->getHttpServer($docroot, $output);
        $httpServer->listen($socketServer);
    }

    /**
     * Run server
     */
    public function run(): void
    {
        $this->output->writeln(sprintf(
            'Starting Sculpin server for the <info>%s</info> environment with debug <info>%s</info>',
            $this->env,
            var_export($this->debug, true)
        ));
        $this->output->writeln(sprintf(
            'Development server is running at <info>http://%s:%s</info>',
            'localhost',
            $this->port
        ));
        $this->output->writeln('Quit the server with CONTROL-C.');

        Loop::run();
    }

    /**
     * Log a request
     *
     * @param OutputInterface           $output       Output
     * @param int                       $responseCode Response code
     * @param ServerRequestInterface    $request      Request
     */
    public static function logRequest(OutputInterface $output, int $responseCode, ServerRequestInterface $request): void
    {
        $wrapOpen  = '';
        $wrapClose = '';

        if ($responseCode >= 400) {
            $wrapOpen  = '<comment>';
            $wrapClose = '</comment>';
        }

        $output->writeln(
            sprintf(
                '%s[%s] "%s %s HTTP/%s" %s%s',
                $wrapOpen,
                date("d/M/Y H:i:s"),
                $request->getMethod(),
                $request->getUri()->getPath(),
                $request->getProtocolVersion(),
                $responseCode,
                $wrapClose
            )
        );
    }

    private function getHttpServer(string $docroot, OutputInterface $output): ReactHttpServer
    {
        $fetcher = $this->fetcher;
        $mimeTypes = new MimeTypes();

        return new ReactHttpServer(function (ServerRequestInterface $request) use (
            $mimeTypes,
            $docroot,
            $fetcher,
            $output
        ) {
            $path = $docroot . '/' . ltrim(rawurldecode($request->getUri()->getPath()), '/');

            if (is_dir($path)) {
                $path = rtrim($path, '/') . '/index.html';
            }

            $response = $fetcher instanceof InBrowserEditorContentFetcher
                ? $fetcher->handleRequest($path, $request, $output)
                : null;

            if ($response) {
                return $response;
            }

            if (!file_exists($path)) {
                HttpServer::logRequest($output, 404, $request);

                $notFoundMessage = '<h1>404</h1><h2>Not Found</h2>'
                    . '<p>'
                    . 'The embedded <a href="https://sculpin.io">Sculpin</a> web server '
                    . 'could not find the requested resource.'
                    . '</p>';

                return new Response(404, ['Content-Type' => 'text/html'], $notFoundMessage);
            }

            $type = 'application/octet-stream';

            if ('' !== $extension = pathinfo($path, PATHINFO_EXTENSION)) {
                $type = $mimeTypes->getMimeTypes($extension)[0] ?? $type;
            }

            HttpServer::logRequest($output, 200, $request);

            return new Response(200, ['Content-Type' => $type], $fetcher->fetchData($path));
        });
    }
}
