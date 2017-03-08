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

use Composer\Downloader\FilesystemException;
use Sculpin\Core\Sculpin;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class SelfUpdateCommand extends AbstractCommand
{
    protected $message = '';
    private $commandPrefix;

    /**
     * {@inheritdoc}
     */
    public function __construct($commandPrefix = 'sculpin:')
    {
        $this->commandPrefix = $this->isStandaloneSculpin()
            ? ''
            : $commandPrefix;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $fullCommand = $this->commandPrefix.'self-update';
        $this
            ->setName($fullCommand)
            ->setAliases(array($this->commandPrefix.'selfupdate'))
            ->setDescription('Updates sculpin to the latest version.')
            ->setHelp(<<<EOT
The <info>self-update</info> command checks for newer versions of sculpin and if found,
installs the latest.

<info>sculpin ${fullCommand}</info>

EOT
            )
        ;
    }

    public function isEnabled()
    {
        return false !== strpos(__DIR__, 'phar:');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $localFilename = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];
        $tempFilename = dirname($localFilename) . '/' . basename($localFilename, '.phar').'-temp.phar';

        // check for permissions in local filesystem before start connection process
        if (!is_writable($tempDirectory = dirname($tempFilename))) {
            throw new FilesystemException(
                'Sculpin update failed: the "'
                . $tempDirectory
                . '" directory used to download the temp file could not be written'
            );
        }

        if (!is_writable($localFilename)) {
            throw new FilesystemException('Sculpin update failed: the "'.$localFilename. '" file could not be written');
        }

        set_error_handler(array($this, 'handleError'));

        $protocol = extension_loaded('openssl') ? 'https' : 'http';

        $versionUrl = $protocol . '://download.sculpin.io/version';

        $latest = trim(file_get_contents($versionUrl, false, $this->getStreamContext()));

        if ($this->message) {
            $output->writeln('<error>Could not determine most recent version:'."\n".$this->message.'</error>');

            return 1;
        }

        if (Sculpin::GIT_VERSION !== $latest) {
            $output->writeln(sprintf("Updating to version <info>%s</info>.", $latest));

            $remoteFilename = $protocol . '://download.sculpin.io/sculpin.phar';

            if (!file_put_contents(
                $tempFilename,
                file_get_contents($remoteFilename, false, $this->getStreamContext())
            )) {
                $output->writeln('<error>The download of the new Sculpin version failed for an unexpected reason');
            }

            if (!file_exists($tempFilename)) {
                $output->writeln('<error>The download of the new Sculpin version failed for an unexpected reason');

                return 1;
            }

            try {
                chmod($tempFilename, 0777 & ~umask());
                // test the phar validity
                $phar = new \Phar($tempFilename);
                // free the variable to unlock the file
                unset($phar);
                rename($tempFilename, $localFilename);
            } catch (\Exception $e) {
                @unlink($tempFilename);
                if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
                    throw $e;
                }
                $output->writeln('<error>The download is corrupted ('.$e->getMessage().').</error>');
                $output->writeln('<error>Please re-run the self-update command to try again.</error>');
            }
        } else {
            $output->writeln("<info>You are using the latest Sculpin version.</info>");
        }

        restore_error_handler();
    }

    /**
     * function copied from Composer\Util\StreamContextFactory::getContext
     *
     * Any changes should be applied there as well, or backported here.
     */
    protected function getStreamContext()
    {
        $options = array('http' => array());

        // Handle system proxy
        if (!empty($_SERVER['HTTP_PROXY']) || !empty($_SERVER['http_proxy'])) {
            // Some systems seem to rely on a lowercased version instead...
            $proxy = parse_url(!empty($_SERVER['http_proxy']) ? $_SERVER['http_proxy'] : $_SERVER['HTTP_PROXY']);
        }

        if (!empty($proxy)) {
            $proxyURL = isset($proxy['scheme']) ? $proxy['scheme'] . '://' : '';
            $proxyURL .= isset($proxy['host']) ? $proxy['host'] : '';

            if (isset($proxy['port'])) {
                $proxyURL .= ":" . $proxy['port'];
            } elseif ('http://' == substr($proxyURL, 0, 7)) {
                $proxyURL .= ":80";
            } elseif ('https://' == substr($proxyURL, 0, 8)) {
                $proxyURL .= ":443";
            }

            // http(s):// is not supported in proxy
            $proxyURL = str_replace(array('http://', 'https://'), array('tcp://', 'ssl://'), $proxyURL);

            if (0 === strpos($proxyURL, 'ssl:') && !extension_loaded('openssl')) {
                throw new \RuntimeException('You must enable the openssl extension to use a proxy over https');
            }

            $options['http'] = array(
                'proxy'           => $proxyURL,
                'request_fulluri' => true,
            );

            if (isset($proxy['user'])) {
                $auth = $proxy['user'];
                if (isset($proxy['pass'])) {
                    $auth .= ':' . $proxy['pass'];
                }
                $auth = base64_encode($auth);

                $options['http']['header'] = "Proxy-Authorization: Basic {$auth}\r\n";
            }
        }

        return stream_context_create($options);
    }

    public function handleError($code, $msg)
    {
        if ($this->message) {
            $this->message .= "\n";
        }
        $this->message .= preg_replace('{^file_get_contents\(.*?\): }', '', $msg);
    }
}
