<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\SculpinBundle\Console;

use Dflydev\EmbeddedComposer\Console\Command\InstallCommand;
use Dflydev\EmbeddedComposer\Console\Command\UpdateCommand;
use Dflydev\EmbeddedComposer\Core\EmbeddedComposer;
use Dflydev\EmbeddedComposer\Core\EmbeddedComposerAwareInterface;
use Sculpin\Core\Sculpin;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Application
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Application extends BaseApplication implements EmbeddedComposerAwareInterface
{
    /**
     * Constructor.
     *
     * @param KernelInterface  $kernel           A KernelInterface instance
     * @param EmbeddedComposer $embeddedComposer Composer Class Loader
     */
    public function __construct(KernelInterface $kernel, EmbeddedComposer $embeddedComposer)
    {
        $this->kernel = $kernel;
        $this->embeddedComposer = $embeddedComposer;

        $version = $embeddedComposer->getPackage()->getPrettyVersion();
        $version = $embeddedComposer->getPackage()->getPrettyVersion();
        $version = $embeddedComposer->getPackage()->getPrettyVersion();

        $version = $embeddedComposer->getPackage()->getPrettyVersion();
        if ($version !== Sculpin::GIT_VERSION && Sculpin::GIT_VERSION !== '@'.'git_version'.'@') {
            $version .= ' ('.Sculpin::GIT_VERSION.')';
        }

        parent::__construct('Sculpin', $version.' - '.$kernel->getName().'/'.$kernel->getEnvironment().($kernel->isDebug() ? '/debug' : ''));

        $this->getDefinition()->addOption(new InputOption('--project-dir', null, InputOption::VALUE_REQUIRED, 'The project directory.', '.'));
        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', $kernel->getEnvironment()));
        $this->getDefinition()->addOption(new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.'));
        $this->getDefinition()->addOption(new InputOption('--safe', null, InputOption::VALUE_NONE, 'Enable safe mode (no bundles loaded, no kernel booted)'));
    }

    /**
     * {@inheritdoc}
     */
    public function getEmbeddedComposer()
    {
        return $this->embeddedComposer;
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption('--safe')) {
            // For safe mode we should enable the Composer
            // commands manually.
            $this->add(new InstallCommand);
            $this->add(new UpdateCommand);
        } else {
            $this->registerCommands();
        }

        parent::doRun($input, $output);
    }

    /**
     * Get Kernel
     *
     * @return Kernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    protected function registerCommands()
    {
        $this->kernel->boot();

        foreach ($this->kernel->getBundles() as $bundle) {
            if ($bundle instanceof BundleInterface) {
                $bundle->registerCommands($this);
            }
        }
    }
}
