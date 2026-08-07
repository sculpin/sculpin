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
namespace Sculpin\Bundle\ContentTypesBundle\Command;

use Sculpin\Bundle\ContentTypesBundle\ContentCreateService;
use Sculpin\Bundle\SculpinBundle\Command\AbstractCommand;
use Sculpin\Bundle\SculpinBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\Inflector\EnglishInflector;

/**
 * Helper command to create a new content type.
 *
 * Outputs the YAML required to add a new content type, and optionally
 * generates the associated boilerplate for the type.
 */
final class ContentCreateCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $prefix = $this->isStandaloneSculpin() ? '' : 'sculpin:';

        $this->setName($prefix . 'content:create');
        $this->setDescription('Create a new content type, including boilerplate template files.');
        $this->setDefinition(
            [
                new InputArgument(
                    'type',
                    InputArgument::REQUIRED,
                    'Name for this type (e.g., "posts")'
                ),
                new InputOption(
                    'boilerplate',
                    'b',
                    InputOption::VALUE_NONE,
                    'Enabled by default. Use --dry-run if you do not want to generate the files.'
                ),
                new InputOption(
                    'dry-run',
                    'd',
                    InputOption::VALUE_NONE,
                    "Don't generate boilerplate/placeholder/template files."
                ),
                new InputOption(
                    'taxonomy',
                    't',
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    "Organize content by taxonomy categories (\"tags\", \"categories\", \"types\", etc)\n"
                    . "Add multiple taxonomies by repeating the option."
                )
            ]
        );

        // phpcs:disable
        $this->setHelp(<<<EOT
            The <info>content:create</info> command helps you create a custom content type and the associated
                  boilerplate/templates.

            Example:

                  vendor/bin/sculpin content:create docs -t product -t year

            NOTE: This command does not automatically modify the <info>app/config/sculpin_kernel.yml</info> file. You
                  will have to add the suggested changes yourself.

            EOT
        );
        // phpcs:enable
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pluralType   = $input->getArgument('type');
        $singularType = new EnglishInflector()->singularize($pluralType)[0];
        $dryRun       = $input->getOption('dry-run');
        $taxonomies   = $input->getOption('taxonomy');

        $output->writeln('Generating new content type: <info>' . $pluralType . '</info>');

        $service = new ContentCreateService($this->getProjectDir());

        // TODO: Prompt the user with a preview before generating content
        $output->writeln($this->getOutputMessage($service, $pluralType, $singularType, $taxonomies));

        // TODO: Write a yaml file to configure the content type (and recommend a wildcard include for types?)

        // grab the boilerplate manifest
        $boilerplateManifest = $service->generateBoilerplateManifest($pluralType, $singularType, $taxonomies);

        // skip creating boilerplate files if this is a dry run
        if ($dryRun) {
            $output->writeln("Dry run. Skipping creation of these boilerplate files:");

            foreach (array_keys($boilerplateManifest) as $filename) {
                $output->writeln("\t<info>" . $filename . '</info>');
            }

            $output->writeln("\nRemember to add the content type definition (displayed above) to sculpin_kernel.yml!");

            return Command::SUCCESS;
        }

        $output->writeln('Generating boilerplate for ' . $pluralType);
        $service->processManifest($boilerplateManifest);
        $output->writeln("\nRemember to add the content type definition (displayed above) to sculpin_kernel.yml!");

        return Command::SUCCESS;
    }

    private function getOutputMessage(
        ContentCreateService $service,
        string $type,
        string $singularType,
        array $taxonomies = []
    ): string {
        $yaml = $service->getYamlString($type, $singularType, $taxonomies);
        $outputMessage = <<<EOT

        YAML content type definition to add to your
        <info>app/config/sculpin_kernel.yml</info> file:
        ================START OF YAML================

        sculpin_content_types:
            {$yaml}
        EOT;

        return $outputMessage . "\n=================END OF YAML=================\n\n";
    }

    public function getProjectDir(): string
    {
        $app = $this->getApplication();
        if (!$app instanceof Application) {
            throw new \RuntimeException('Sculpin Application not found!');
        }

        return \dirname($app->getKernel()->getProjectDir());
    }
}
