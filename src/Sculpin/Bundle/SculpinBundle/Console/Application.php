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

namespace Sculpin\Bundle\SculpinBundle\Console;

use Sculpin\Bundle\SculpinBundle\HttpKernel\AbstractKernel;
use Sculpin\Core\Sculpin;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final class Application extends BaseApplication
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var \Throwable[]
     */
    private $registrationErrors = [];

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
            date_default_timezone_set(@date_default_timezone_get());
        }

        parent::__construct(
            'Sculpin',
            $kernel->getName() . '/' . $kernel->getEnvironment() . ($kernel->isDebug() ? '/debug' : '')
        );

        $this->getDefinition()->addOption(new InputOption(
            '--project-dir',
            null,
            InputOption::VALUE_REQUIRED,
            'The project directory.',
            '.'
        ));
        $this->getDefinition()->addOption(new InputOption(
            '--env',
            '-e',
            InputOption::VALUE_REQUIRED,
            'The Environment name.',
            $kernel->getEnvironment()
        ));
        $this->getDefinition()->addOption(new InputOption(
            '--no-debug',
            null,
            InputOption::VALUE_NONE,
            'Switches off debug mode.'
        ));
        $this->getDefinition()->addOption(new InputOption(
            '--safe',
            null,
            InputOption::VALUE_NONE,
            'Enable safe mode (no bundles loaded, no kernel booted)'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        if (null === $output) {
            $styles = [
                'highlight' => new OutputFormatterStyle('red'),
                'warning' => new OutputFormatterStyle('black', 'yellow'),
            ];
            $formatter = new OutputFormatter(false, $styles);
            $output = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, null, $formatter);
        }

        return parent::run($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->hasParameterOption('--safe')) {
            // In safe mode enable no commands
            $this->registerCommands();

            if ($this->registrationErrors) {
                $this->renderRegistrationErrors($input, $output);
            }
        }

        $exitCode = parent::doRun($input, $output);

        foreach ($this->getMissingSculpinBundlesMessages() as $message) {
            $output->writeln($message);
        }

        return $exitCode;
    }

    public function getMissingSculpinBundlesMessages(): array
    {
        if (!$this->kernel instanceof AbstractKernel) {
            return [];
        }

        $messages = [];

        // Display missing bundle to user.
        if ($missingBundles = $this->kernel->getMissingSculpinBundles()) {
            $messages[] = '';
            $messages[] = '<comment>Missing Sculpin Bundles:</comment>';
            foreach ($missingBundles as $bundle) {
                $messages[] = "  * <highlight>$bundle</highlight>";
            }
            $messages[] = '';
        }

        return $messages;
    }

    /**
     * Get Kernel
     *
     * @return KernelInterface
     */
    public function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    private function registerCommands(): void
    {
        $this->kernel->boot();

        foreach ($this->kernel->getBundles() as $bundle) {
            if ($bundle instanceof Bundle) {
                $bundle->registerCommands($this);
            }
        }

        $container = $this->kernel->getContainer();

        if ($container->has('console.command_loader')) {
            $this->setCommandLoader($container->get('console.command_loader'));
        }

        if (!$container->hasParameter('console.command.ids')) {
            return;
        }

        $lazyCommandIds = $container->hasParameter('console.lazy_command.ids')
            ? $container->getParameter('console.lazy_command.ids')
            : [];

        foreach ($container->getParameter('console.command.ids') as $id) {
            if (!isset($lazyCommandIds[$id])) {
                try {
                    $this->add($container->get($id));
                } catch (\Exception $e) {
                    $this->registrationErrors[] = $e;
                } catch (\Throwable $e) {
                    $this->registrationErrors[] = new FatalThrowableError($e);
                }
            }
        }
    }

    private function renderRegistrationErrors(InputInterface $input, OutputInterface $output): void
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        (new SymfonyStyle($input, $output))->warning('Some commands could not be registered:');

        foreach ($this->registrationErrors as $error) {
            $this->doRenderException($error, $output);
        }
    }
}
