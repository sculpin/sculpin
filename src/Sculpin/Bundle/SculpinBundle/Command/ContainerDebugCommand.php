<?php

declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This command is derived from the Symfony container debug command,
 * (c) Fabien Potencier <fabien@symfony.com>
 */

namespace Sculpin\Bundle\SculpinBundle\Command;

use Sculpin\Core\Console\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * A console command for retrieving information about services
 *
 * @author Ryan Weaver <ryan@thatsquality.com>
 */
final class ContainerDebugCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('container:debug')
            ->setDefinition([
                new InputArgument('name', InputArgument::OPTIONAL, 'A service name (foo)'),
                new InputOption(
                    'show-private',
                    null,
                    InputOption::VALUE_NONE,
                    'Use to show public *and* private services'
                ),
                new InputOption(
                    'tag',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Show all services with a specific tag'
                ),
                new InputOption(
                    'tags',
                    null,
                    InputOption::VALUE_NONE,
                    'Displays tagged services for an application'
                ),
                new InputOption(
                    'parameter',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Displays a specific parameter for an application'
                ),
                new InputOption(
                    'parameters',
                    null,
                    InputOption::VALUE_NONE,
                    'Displays parameters for an application'
                )
            ])
            ->setDescription('Displays current services for an application')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command displays all configured <comment>public</comment> services:

  <info>php %command.full_name%</info>

To get specific information about a service, specify its name:

  <info>php %command.full_name% validator</info>

By default, private services are hidden. You can display all services by
using the --show-private flag:

  <info>php %command.full_name% --show-private</info>

Use the --tags option to display tagged <comment>public</comment> services grouped by tag:

  <info>php %command.full_name% --tags</info>

Find all services with a specific tag by specifying the tag name with the --tag option:

  <info>php %command.full_name% --tag=form.type</info>

Use the --parameters option to display all parameters:

  <info>php %command.full_name% --parameters</info>

Display a specific parameter by specifying his name with the --parameter option:

  <info>php %command.full_name% --parameter=kernel.debug</info>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateInput($input);

        if ($input->getOption('parameters')) {
            if (!$this->getContainer() instanceof Container) {
                return 1;
            }
            $parameters = $this->getContainer()->getParameterBag()->all();

            // Sort parameters alphabetically
            ksort($parameters);

            $this->outputParameters($output, $parameters);

            return 0;
        }

        $parameter = $input->getOption('parameter');
        if (null !== $parameter) {
            $output->write($this->formatParameter($this->getContainer()->getParameter($parameter)));

            return 0;
        }

        if ($input->getOption('tags')) {
            $this->outputTags($output, $input->getOption('show-private'));

            return 0;
        }

        $tag = $input->getOption('tag');
        $serviceIds = [];
        if ($this->getContainer() instanceof ContainerBuilder) {
            if (null !== $tag) {
                $serviceIds = array_keys($this->getContainer()->findTaggedServiceIds($tag));
            } else {
                $serviceIds = $this->getContainer()->getServiceIds();
            }
        }

        // sort so that it reads like an index of services
        asort($serviceIds);

        $name = $input->getArgument('name');
        if ($name) {
            $this->outputService($output, $name);
        } else {
            $this->outputServices($output, $serviceIds, $input->getOption('show-private'), $tag);
        }

        return 0;
    }

    private function validateInput(InputInterface $input)
    {
        $options = ['tags', 'tag', 'parameters', 'parameter'];

        $optionsCount = 0;
        foreach ($options as $option) {
            if ($input->getOption($option)) {
                $optionsCount++;
            }
        }

        $name = $input->getArgument('name');
        if ((null !== $name) && ($optionsCount > 0)) {
            throw new \InvalidArgumentException(
                'The options tags, tag, parameters & parameter can not be combined with the service name argument.'
            );
        } elseif ((null === $name) && $optionsCount > 1) {
            throw new \InvalidArgumentException(
                'The options tags, tag, parameters & parameter can not be combined together.'
            );
        }
    }

    private function outputServices(
        OutputInterface $output,
        $serviceIds,
        $showPrivate = false,
        $showTagAttributes = null
    ): void {
        // set the label to specify public or public+private
        if ($showPrivate) {
            $label = '<comment>Public</comment> and <comment>private</comment> services';
        } else {
            $label = '<comment>Public</comment> services';
        }
        if ($showTagAttributes) {
            $label .= ' with tag <info>'.$showTagAttributes.'</info>';
        }

        $output->writeln($this->getHelper('formatter')->formatSection('container', $label));

        // loop through to get space needed and filter private services
        $maxName = 4;
        $maxTags = [];
        foreach ($serviceIds as $key => $serviceId) {
            $definition = $this->resolveServiceDefinition($serviceId);

            if ($definition instanceof Definition) {
                // filter out private services unless shown explicitly
                if (!$showPrivate && !$definition->isPublic()) {
                    unset($serviceIds[$key]);
                    continue;
                }

                if (null !== $showTagAttributes) {
                    $tags = $definition->getTag($showTagAttributes);
                    foreach ($tags as $tag) {
                        foreach ($tag as $key => $value) {
                            if (!isset($maxTags[$key])) {
                                $maxTags[$key] = strlen($key);
                            }
                            if (strlen($value) > $maxTags[$key]) {
                                $maxTags[$key] = strlen($value);
                            }
                        }
                    }
                }
            }

            if (strlen($serviceId) > $maxName) {
                $maxName = strlen($serviceId);
            }
        }
        $format = '%-'.$maxName.'s ';
        $format .= implode("", array_map(function ($length) {
            return "%-{$length}s ";
        }, $maxTags));
        $format .=  '%s';

        // the title field needs extra space to make up for comment tags
        $format1 = '%-'.($maxName + 19).'s ';
        $format1 .= implode("", array_map(function ($length) {
            return '%-'.($length + 19).'s ';
        }, $maxTags));
        $format1 .= '%s';

        $tags = [];
        foreach ($maxTags as $tagName => $length) {
            $tags[] = '<comment>'.$tagName.'</comment>';
        }
        $output->writeln(vsprintf($format1, $this->buildArgumentsArray(
            '<comment>Service Id</comment>',
            '<comment>Class Name</comment>',
            $tags
        )));

        foreach ($serviceIds as $serviceId) {
            $definition = $this->resolveServiceDefinition($serviceId);

            if ($definition instanceof Definition) {
                $lines = [];
                if (null !== $showTagAttributes) {
                    foreach ($definition->getTag($showTagAttributes) as $key => $tag) {
                        $tagValues = [];
                        foreach (array_keys($maxTags) as $tagName) {
                            $tagValues[] = isset($tag[$tagName]) ? $tag[$tagName] : "";
                        }
                        if (0 === $key) {
                            $lines[] = $this->buildArgumentsArray(
                                $serviceId,
                                $definition->getClass(),
                                $tagValues
                            );
                        } else {
                            $lines[] = $this->buildArgumentsArray('  "', '', $tagValues);
                        }
                    }
                } else {
                    $lines[] = $this->buildArgumentsArray($serviceId, $definition->getClass());
                }

                foreach ($lines as $arguments) {
                    $output->writeln(vsprintf($format, $arguments));
                }
            } elseif ($definition instanceof Alias) {
                $alias = $definition;
                $output->writeln(vsprintf($format, $this->buildArgumentsArray(
                    $serviceId,
                    sprintf('<comment>alias for</comment> <info>%s</info>', (string) $alias),
                    count($maxTags) ? array_fill(0, count($maxTags), "") : []
                )));
            } else {
                // we have no information (happens with "service_container")
                $service = $definition;
                $output->writeln(vsprintf($format, $this->buildArgumentsArray(
                    $serviceId,
                    get_class($service),
                    count($maxTags) ? array_fill(0, count($maxTags), "") : []
                )));
            }
        }
    }

    private function buildArgumentsArray($serviceId, $className, array $tagAttributes = []): array
    {
        $arguments = [$serviceId];
        foreach ($tagAttributes as $tagAttribute) {
            $arguments[] = $tagAttribute;
        }
        $arguments[] = $className;

        return $arguments;
    }

    /**
     * Renders detailed service information about one service
     */
    private function outputService(OutputInterface $output, string $serviceId)
    {
        $definition = $this->resolveServiceDefinition($serviceId);

        $label = sprintf('Information for service <info>%s</info>', $serviceId);
        $output->writeln($this->getHelper('formatter')->formatSection('container', $label));
        $output->writeln('');

        if ($definition instanceof Definition) {
            $output->writeln(sprintf('<comment>Service Id</comment>       %s', $serviceId));
            $output->writeln(sprintf('<comment>Class</comment>            %s', $definition->getClass() ?: "-"));

            $tags = $definition->getTags();
            if (count($tags)) {
                $output->writeln('<comment>Tags</comment>');
                foreach ($tags as $tagName => $tagData) {
                    foreach ($tagData as $singleTagData) {
                        $output->writeln(sprintf(
                            '    - %-30s (%s)',
                            $tagName,
                            implode(', ', array_map(
                                function ($key, $value) {
                                    return sprintf('<info>%s</info>: %s', $key, $value);
                                },
                                array_keys($singleTagData),
                                array_values($singleTagData)
                            ))
                        ));
                    }
                }
            } else {
                $output->writeln('<comment>Tags</comment>             -');
            }

            $public = $definition->isPublic() ? 'yes' : 'no';
            $output->writeln(sprintf('<comment>Public</comment>           %s', $public));

            $synthetic = $definition->isSynthetic() ? 'yes' : 'no';
            $output->writeln(sprintf('<comment>Synthetic</comment>        %s', $synthetic));

            $file = $definition->getFile() ? $definition->getFile() : '-';
            $output->writeln(sprintf('<comment>Required File</comment>    %s', $file));
        } elseif ($definition instanceof Alias) {
            $alias = $definition;
            $output->writeln(sprintf('This service is an alias for the service <info>%s</info>', (string) $alias));
        } else {
            // edge case (but true for "service_container", all we have is the service itself
            $service = $definition;
            $output->writeln(sprintf('<comment>Service Id</comment>   %s', $serviceId));
            $output->writeln(sprintf('<comment>Class</comment>        %s', get_class($service)));
        }
    }

    private function outputParameters(OutputInterface $output, array $parameters): void
    {
        $output->writeln($this->getHelper('formatter')->formatSection('container', 'List of parameters'));

        $maxTerminalWidth   = (int) (getenv('COLUMNS') ?? 80);
        $maxParameterWidth  = 0;
        $maxValueWidth      = 0;

        // Determine max parameter & value length
        foreach ($parameters as $parameter => $value) {
            $parameterWidth = strlen($parameter);
            if ($parameterWidth > $maxParameterWidth) {
                $maxParameterWidth = $parameterWidth;
            }

            $valueWith = strlen($this->formatParameter($value));
            if ($valueWith > $maxValueWidth) {
                $maxValueWidth = $valueWith;
            }
        }

        $maxValueWidth = min($maxValueWidth, $maxTerminalWidth - $maxParameterWidth - 1);

        $formatTitle = '%-'.($maxParameterWidth + 19).'s %-'.($maxValueWidth + 19).'s';
        $format = '%-'.$maxParameterWidth.'s %-'.$maxValueWidth.'s';

        $output->writeln(sprintf($formatTitle, '<comment>Parameter</comment>', '<comment>Value</comment>'));

        foreach ($parameters as $parameter => $value) {
            $splits = str_split($this->formatParameter($value), $maxValueWidth);

            foreach ($splits as $index => $split) {
                if (0 === $index) {
                    $output->writeln(sprintf($format, $parameter, $split));
                } else {
                    $output->writeln(sprintf($format, ' ', $split));
                }
            }
        }
    }

    /**
     * Given an array of service IDs, this returns the array of corresponding
     * Definition and Alias objects that those ids represent.
     *
     * @param string $serviceId The service id to resolve
     *
     * @return Definition|Alias
     * @throws \Exception
     */
    private function resolveServiceDefinition($serviceId)
    {
        $container = $this->getContainer();
        if ($container instanceof ContainerBuilder) {
            if ($container->hasDefinition($serviceId)) {
                return $container->getDefinition($serviceId);
            }

            // Some service IDs don't have a Definition, they're simply an Alias
            if ($container->hasAlias($serviceId)) {
                return $container->getAlias($serviceId);
            }
        }

        // the service has been injected in some special way, just return the service
        return $container->get($serviceId);
    }

    /**
     * Renders list of tagged services grouped by tag
     *
     * @param OutputInterface $output
     * @param bool $showPrivate
     *
     * @throws \Exception
     */
    private function outputTags(OutputInterface $output, bool $showPrivate = false): void
    {
        $container = $this->getContainer();
        if (! $container instanceof ContainerBuilder) {
            return;
        }
        $tags = $container->findTags();
        asort($tags);

        $label = 'Tagged services';
        $output->writeln($this->getHelper('formatter')->formatSection('container', $label));

        foreach ($tags as $tag) {
            $serviceIds = $container->findTaggedServiceIds($tag);

            foreach ($serviceIds as $serviceId => $attributes) {
                $definition = $this->resolveServiceDefinition($serviceId);
                if ($definition instanceof Definition) {
                    if (!$showPrivate && !$definition->isPublic()) {
                        unset($serviceIds[$serviceId]);
                        continue;
                    }
                }
            }

            if (count($serviceIds) === 0) {
                continue;
            }

            $output->writeln($this->getHelper('formatter')->formatSection('tag', $tag));

            foreach ($serviceIds as $serviceId => $attributes) {
                $output->writeln($serviceId);
            }

            $output->writeln('');
        }
    }

    private function formatParameter($value)
    {
        if (is_bool($value) || is_array($value) || (null === $value)) {
            return json_encode($value);
        }

        return $value;
    }
}
