<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\console\command;

use Symfony\Component\Yaml\Yaml;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationDumpCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('configuration:dump')
            ->setDescription('Dumps site configuration.')
            ->setDefinition(array(
                new InputOption('as', null, InputOption::VALUE_REQUIRED, 'as format', 'yaml'),
                new InputOption('to', null, InputOption::VALUE_REQUIRED, 'to file', 'STDOUT'),
                new InputOption('force', null, InputOption::VALUE_NONE, 'force writing to file even if file exists'),
            ))
            ->setHelp(<<<EOT
The <info>configuration:dump</info> command dumps the compiled configuration.
EOT
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $as = $input->getOption('as');
        $to = $input->getOption('to');
        $force = (Boolean) $input->getOption('force');
        $dump = null;
        switch($as) {
            case 'yaml':
                // TODO: This could use some serious refactoring
                $sculpin = $this->getSculpinApplication()->createSculpin();
                $sculpin->start();
                $dump = Yaml::dump($sculpin->configuration()->export());
                break;
            default:
                $output->writeln(array(<<<EOT
<error>[Unsupported Format]</error> Requested format ${as} is not recognized
EOT
                ));
                return;
                break;
        }
        switch($to) {
            case 'STDOUT':
                $output->write(array($dump));
                break;
            default:
                if (file_exists($to) and !$force) {
                    $output->writeln(array(<<<EOT
<error>[Output File Exists]</error> Specified file ${to} exists, use --force overwrite
EOT
                    ));
                    return;
                }
                $output->writeln(array(<<<EOT
<info>[Configuration Dumped]</info> Site configuration written to "${to}"
EOT
                ));
                file_put_contents($to, $dump);
        }
    }
}
