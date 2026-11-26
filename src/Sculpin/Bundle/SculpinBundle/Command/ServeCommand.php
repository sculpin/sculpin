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

namespace Sculpin\Bundle\SculpinBundle\Command;

use Sculpin\Bundle\SculpinBundle\HttpServer\DefaultContentFetcher;
use Symfony\Component\Console\Command\Command;
use Sculpin\Bundle\SculpinBundle\HttpServer\HttpServer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class ServeCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $prefix = $this->isStandaloneSculpin() ? '' : 'sculpin:';

        $this
            ->setName($prefix.'serve')
            ->setDescription('Serve a site.')
            ->setDefinition([
                self::getHostInputOption(),
                new InputOption('port', null, InputOption::VALUE_REQUIRED, 'Port'),
            ])
            ->setHelp(<<<EOT
            The <info>serve</info> command serves a site.

            EOT
            )->setAliases(['server']);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $docroot = $this->getContainer()->getParameter('sculpin.output_dir');
        $kernel = $this->getContainer()->get('kernel');

        $hostIp = (string) ($input->getOption('host') ?? HttpServer::DEFAULT_HOST);

        if ($hostIp && !self::validateInterfaceIp($hostIp)) {
            $output->writeln('<error>Invalid Host IP Provided. Must be a valid IPv4 or [IPv6] string, or "localhost".</error>');

            return Command::FAILURE;
        }

        $httpServer = new HttpServer(
            output: $output,
            fetcher: new DefaultContentFetcher(),
            docroot: $docroot,
            env: $kernel->getEnvironment(),
            debug: $kernel->isDebug(),
            host: $hostIp,
            port: (int) ($input->getOption('port') ?? HttpServer::DEFAULT_PORT)
        );

        $httpServer->run();

        return Command::SUCCESS;
    }

    public static function getHostInputOption(): InputOption
    {
        return new InputOption(
            'host',
            null,
            InputOption::VALUE_REQUIRED,
            <<<EOT
            Host Interface IP Address:
            - 127.0.0.1 to prevent external access
            - 0.0.0.0 or 'localhost' to allow access on all interfaces
            - "[::1]" to listen on IPv6 (host MUST be enclosed in square brackets for IPv6)

            EOT,
            '127.0.0.1',
        );
    }

    public static function validateInterfaceIp(string $ip): bool
    {
        return match (true) {
            $ip === 'localhost',
            false !== filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4),
            false !== filter_var(trim($ip, '[]'), \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)
                    => true,
            default => false,
        };
    }
}
