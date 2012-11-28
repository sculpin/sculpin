<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\Command;

use React\EventLoop\StreamSelectLoop;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Serve Command.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class ServeCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $prefix = $this->isStandaloneSculpin() ? '' : 'sculpin:';

        $this
            ->setName($prefix.'serve')
            ->setDescription('Serve a site.')
            ->setDefinition(array(
                new InputOption('host', null, InputOption::VALUE_REQUIRED, 'Host'),
                new InputOption('port', null, InputOption::VALUE_REQUIRED, 'Port'),
            ))
            ->setHelp(<<<EOT
The <info>serve</info> command serves a site.
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $docroot = $this->getContainer()->getParameter('sculpin.output_dir');
        $loop = new StreamSelectLoop;
        $socketServer = new SocketServer($loop);
        $httpServer = new HttpServer($socketServer);

        $self = $this;

        $httpServer->on("request", function($request, $response) use ($docroot, $output) {
            $path = $docroot.'/'.ltrim($request->getPath(), '/');
            if (is_dir($path)) {
                $path .= '/index.html';
            }
            if (!file_exists($path)) {
                ServeCommand::logRequest($output, 404, $request);
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

            ServeCommand::logRequest($output, 200, $request);

            $response->writeHead(200, array(
                "Content-Type" => $type,
            ));
            $response->end(file_get_contents($path));
        });

        $port = $input->getOption('port') ?: '8000';
        $host = $input->getOption('host') ?: 'localhost';

        $socketServer->listen($port, $host);

        $kernel = $this->getContainer()->get('kernel');

        $output->writeln(sprintf('Starting Sculpin server for the <info>%s</info> environment with debug <info>%s</info>', $kernel->getEnvironment(), var_export($kernel->isDebug(), true)));
        $output->writeln(sprintf('Development server is running at <info>http://%s:%s</info>', $host, $port));
        $output->writeln('Quit the server with CONTROL-C.');

        $loop->run();
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
