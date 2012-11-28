<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\HttpServer;

use React\EventLoop\StreamSelectLoop;
use React\Http\Server as ReactHttpServer;
use React\Socket\Server as ReactSocketServer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * HTTP Server
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class HttpServer
{
    /**
     * Constructor
     *
     * @param OutputInterface $output  Output
     * @param string          $docroot Docroot
     * @param string          $env     Environment
     * @param bool            $debug   Debug
     * @param int             $port    Port
     */
    public function __construct(OutputInterface $output, $docroot, $env, $debug, $port = null)
    {
        if (!$port) {
            $port = 8000;
        }

        $this->output = $output;
        $this->env = $env;
        $this->debug = $debug;
        $this->port = $port;

        $this->loop = new StreamSelectLoop;
        $socketServer = new ReactSocketServer($this->loop);
        $httpServer = new ReactHttpServer($socketServer);
        $httpServer->on("request", function($request, $response) use ($docroot, $output) {
            $path = $docroot.'/'.ltrim($request->getPath(), '/');
            if (is_dir($path)) {
                $path .= '/index.html';
            }
            if (!file_exists($path)) {
                HttpServer::logRequest($output, 404, $request);
                $response->writeHead(404);

                return $response->end();
            }

            if (function_exists('finfo_file')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $type = finfo_file($finfo, $path);
                finfo_close($finfo);
            } else {
                $type = 'text/plain';
            }

            if (!$type || in_array($type, array('application/octet-stream', 'text/plain'))) {
                $secondOpinion = exec('file -b --mime-type ' . escapeshellarg($path), $foo, $returnCode);
                if ($returnCode === 0 && $secondOpinion) {
                    $type = $secondOpinion;
                }
            }

            if (in_array($type, array('text/plain', 'text/x-c')) && preg_match('/\.css$/', $path)) {
                $type = 'text/css';
            }

            HttpServer::logRequest($output, 200, $request);

            $response->writeHead(200, array(
                "Content-Type" => $type,
            ));
            $response->end(file_get_contents($path));
        });

        $socketServer->listen($port, '0.0.0.0');
    }

    public function addPeriodicTimer($interval, $callback)
    {
        $this->loop->addPeriodicTimer($interval, $callback);
    }

    public function run()
    {
        $this->output->writeln(sprintf('Starting Sculpin server for the <info>%s</info> environment with debug <info>%s</info>', $this->env, var_export($this->debug, true)));
        $this->output->writeln(sprintf('Development server is running at <info>http://%s:%s</info>', 'localhost', $this->port));
        $this->output->writeln('Quit the server with CONTROL-C.');

        $this->loop->run();
    }

    static public function logRequest(OutputInterface $output, $responseCode, $request)
    {
        if ($responseCode < 400) {
            $wrapOpen = '';
            $wrapClose = '';
        } elseif ($responseCode >= 400) {
            $wrapOpen = '<comment>';
            $wrapClose = '</comment>';
        }
        $output->writeln($wrapOpen.sprintf('[%s] "%s %s HTTP/%s" %s', date("d/M/Y H:i:s"), $request->getMethod(), $request->getPath(), $request->getHttpVersion(), $responseCode).$wrapClose);
    }
}
