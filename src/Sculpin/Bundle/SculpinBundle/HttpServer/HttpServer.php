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
use React\EventLoop\StreamSelectLoop;
use React\Http\Message\Response;
use React\Http\Server as ReactHttpServer;
use React\Socket\Server as ReactSocketServer;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mime\MimeTypes;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class HttpServer
{
    public const int DEFAULT_PORT = 8000;

    private StreamSelectLoop $loop;

    public function __construct(
        private OutputInterface $output,
        string $docroot,
        private string $env,
        private bool $debug,
        private int $port = self::DEFAULT_PORT
    ) {
        $socketServer = $this->startSocketServer();
        $httpServer = $this->getHttpServer($docroot, $output);

        $httpServer->listen($socketServer);
    }

    /**
     * Add a periodic timer
     *
     * @param int      $interval Interval
     * @param callable $callback Callback
     */
    public function addPeriodicTimer(int $interval, callable $callback): void
    {
        $this->loop->addPeriodicTimer($interval, $callback);
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

        $this->loop->run();
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

    private function startSocketServer(): ReactSocketServer
    {
        $this->loop = new StreamSelectLoop;

        return new ReactSocketServer(
            sprintf('0.0.0.0:%d', $this->port),
            $this->loop
        );
    }

    private function getHttpServer(string $docroot, OutputInterface $output): ReactHttpServer
    {
        return new ReactHttpServer($this->loop, function (ServerRequestInterface $request) use (
            $docroot,
            $output
        ) {
            $mimeTypes = new MimeTypes();
            $path = $docroot . '/' . ltrim(rawurldecode($request->getUri()->getPath()), '/');

            if (is_dir($path)) {
                $path = rtrim($path, '/') . '/index.html';
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

            return new Response(200, ['Content-Type' => $type], file_get_contents($path));
        });
    }
}
