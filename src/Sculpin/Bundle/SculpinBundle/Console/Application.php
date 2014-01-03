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

use Dflydev\EmbeddedComposer\Core\EmbeddedComposer;
use Dflydev\EmbeddedComposer\Core\EmbeddedComposerAwareInterface;
use Sculpin\Core\Sculpin;
use Sculpin\Bundle\SculpinBundle\Command\DumpAutoloadCommand;
use Sculpin\Bundle\SculpinBundle\Command\InstallCommand;
use Sculpin\Bundle\SculpinBundle\Command\SelfUpdateCommand;
use Sculpin\Bundle\SculpinBundle\Command\UpdateCommand;
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

        $version = $embeddedComposer->findPackage('sculpin/sculpin')->getPrettyVersion();
        if ($version !== Sculpin::GIT_VERSION && Sculpin::GIT_VERSION !== '@'.'git_version'.'@') {
            $version .= ' ('.Sculpin::GIT_VERSION.')';
        }

        parent::__construct('Sculpin', $version.' - '.$kernel->getName().'/'.$kernel->getEnvironment().($kernel->isDebug() ? '/debug' : ''));

        $this->getDefinition()->addOption(new InputOption('--project-dir', null, InputOption::VALUE_REQUIRED, 'The project directory.', '.'));
        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', $kernel->getEnvironment()));
        $this->getDefinition()->addOption(new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.'));
        $this->getDefinition()->addOption(new InputOption('--safe', null, InputOption::VALUE_NONE, 'Enable safe mode (no bundles loaded, no kernel booted)'));
        $this->getDefinition()->addOption(new InputOption('--git-version', null, InputOption::VALUE_NONE, 'See Git version'));
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
        if ($input->hasParameterOption('--git-version')) {
            $output->writeln(Sculpin::GIT_VERSION);

            return;
        }

        if ($input->hasParameterOption('--safe')) {
            // For safe mode we should enable some commands
            // manually because we won't enable any others.
            $this->add(new DumpAutoloadCommand(''));
            $this->add(new InstallCommand(''));
            $this->add(new SelfUpdateCommand(''));
            $this->add(new UpdateCommand(''));
        } else {
            $this->registerCommands();
        }

        // Display missing bundle to user.
        if ($missing_bundle = $this->kernel->getMissingSculpinBundles()) {
            $output->writeln('<error>There are some missing bundle:</error>');
            foreach ($missing_bundle as $bundle) {
                $output->writeln(" + $bundle");
            }
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
