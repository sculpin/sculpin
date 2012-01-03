<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\console;

use sculpin\configuration\Util;

use sculpin\configuration\Configuration;

use sculpin\configuration\YamlConfigurationBuilder;

use sculpin\Sculpin;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('Sculpin', Sculpin::VERSION);
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->add(new command\InitCommand());
        $this->add(new command\GenerateCommand());
        return parent::doRun($input, $output);
    }

    public function createSculpin() {
        $configurationBuilder = new YamlConfigurationBuilder(array(
            __DIR__.'/../resources/configuration/sculpin.yml',
            'sculpin.yml.dist',
            'sculpin.yml',
        ));
        $configuration = $configurationBuilder->build();
        return new Sculpin($configuration);
    }

}
