<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\ContentTypesBundle\Command;

use Doctrine\Common\Inflector\Inflector;
use Sculpin\Bundle\SculpinBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Content Type Creation Command.
 *
 * Outputs the YAML required to add a new content type, and optionally
 * generates the associated boilerplate for the type.
 */
class ContentCreateCommand extends AbstractCommand
{
    const DIRECTORY_FLAG = '_directory_';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $prefix = $this->isStandaloneSculpin() ? '' : 'sculpin:';

        $this->setName($prefix . 'content:create');
        $this->setDescription('Create a new content type.');
        $this->setDefinition(
            array(
                new InputArgument(
                    'type',
                    InputArgument::REQUIRED,
                    'Name for this type (e.g., "posts")'
                ),
                new InputOption(
                    'boilerplate',
                    'b',
                    InputOption::VALUE_NONE,
                    'Generate boilerplate/placeholder/template files.'
                ),
                new InputOption(
                    'taxonomy',
                    't',
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'Organize content by taxonomy categories ("tags", "categories", "types", etc)'
                )
            )
        );

        $this->setHelp(<<<EOT
The <info>content:create</info> command helps you create a custom content type and,
optionally, the associated boilerplate/templates.

EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluralType   = $input->getArgument('type');
        $singularType = Inflector::singularize($pluralType);
        $boilerplate  = $input->getOption('boilerplate');
        $taxonomies   = $input->getOption('taxonomy');

        $output->writeln('Generating new content type: <info>' . $pluralType . '</info>');

        // TODO: Prompt the user with a preview before generating content
        $output->writeln($this->getOutputMessage($pluralType, $singularType, $taxonomies));

        // TODO: Write a yaml file to configure the content type (and recommend a wildcard include for types?)

        if ($boilerplate) {
            $output->writeln('Generating boilerplate for ' . $pluralType);
            $boilerplateManifest = $this->generateBoilerplateManifest($pluralType, $singularType, $taxonomies);

            $fs = new Filesystem();
            foreach ($boilerplateManifest as $filename => $value) {
                // create directory and skip the rest of the loop
                if ($value === static::DIRECTORY_FLAG) {
                    $fs->mkdir($filename);
                    continue;
                }

                if ($fs->exists($filename)) {
                    $output->writeln('Warning: ' . $filename . ' already exists at the target location. Skipping.');
                    continue;
                }

                // create file $filename with contents $value
                $fs->dumpFile($filename, $value);
            }
        }
    }

    protected function generateBoilerplateManifest($plural, $singular, $taxonomies = [])
    {
        $rootDir  = dirname($this->getApplication()->getKernel()->getRootDir());
        $manifest = [];

        // ensure the content type storage folder exists
        $storageFolder            = $rootDir . '/source/_' . $plural;
        $manifest[$storageFolder] = static::DIRECTORY_FLAG;

        // content type index template
        $index            = $rootDir . '/source/' . $plural . '.html';
        $manifest[$index] = $this->getIndexTemplate($plural, $singular);

        // ensure the views folder exists
        $storageFolder            = $rootDir . '/source/_views';
        $manifest[$storageFolder] = static::DIRECTORY_FLAG;

        // content type view template
        $index            = $rootDir . '/source/_views/' . $singular . '.html';
        $manifest[$index] = $this->getViewTemplate($plural, $singular, $taxonomies);

        foreach ($taxonomies as $taxonomy) {
            $singularTaxonomy = Inflector::singularize($taxonomy);
            // content taxonomy index template
            $index            = $rootDir . '/source/' . $plural . '/' . $taxonomy . '.html';
            $manifest[$index] = $this->getTaxonomyIndexTemplate($plural, $singular, $taxonomy, $singularTaxonomy);

            // content taxonomy directory
            $storageFolder            = $rootDir . '/source/' . $plural . '/' . $taxonomy;
            $manifest[$storageFolder] = static::DIRECTORY_FLAG;

            // content taxonomy view template(s)
            $index            = $rootDir . '/source/' . $plural . '/' . $taxonomy . '/' . $singularTaxonomy . '.html';
            $manifest[$index] = $this->getTaxonomyViewTemplate($plural, $singular, $taxonomy, $singularTaxonomy);
        }

        return $manifest;
    }

    protected function getOutputMessage($type, $singularType, $taxonomies = [])
    {
        $outputMessage = <<<EOT
=============================================
YAML to add to <info>app/config/sculpin_kernel.yml</info>:
=============================================

sculpin_content_types:
    ${type}:
        type: path
        path: _${type}
        singular_name: ${singularType}
        layout: ${singularType}
        enabled: true
        permalink: ${type}/:title
EOT;
        if ($taxonomies) {
            $outputMessage .= "\n        taxonomies:\n";
            foreach ($taxonomies as $taxonomy) {
                $outputMessage .= "            - ${taxonomy}\n";
            }
        }

        $outputMessage .= "\n=================END OF YAML=================\n\n";

        return $outputMessage;
    }

    protected function getIndexTemplate($plural, $singular)
    {
        $title = ucfirst($plural);

        return <<<EOT
---
layout: default
title: $title
generator: pagination
pagination:
    provider: data.$plural
    max_per_page: 10
use: [$plural]
---
<ul>
    {% for $singular in page.pagination.items %}
        <li><a href="{{ $singular.url }}">{{ $singular.title }}</a></li>
    {% endfor %}
</ul>

<nav>
    {% if page.pagination.previous_page or page.pagination.next_page %}
    {% if page.pagination.previous_page %}
    <a href="{{ site.url }}{{ page.pagination.previous_page.url }}">Newer ${plural}</a>
    {% endif %}
    {% if page.pagination.next_page %}
    <a href="{{ site.url }}{{ page.pagination.next_page.url }}">Older ${plural}</a>
    {% endif %}
    {% endif %}
</nav>
EOT;
    }

    protected function getViewTemplate($plural, $singular, $taxonomies = [])
    {
        $output = <<<EOT
{% extends 'default' %}

{% block content_wrapper %}
<article>
  <header>
    <h2>{{ page.title }}</h2>
  {% if page.subtitle %}
    <h3 class="subtitle">{{ page.subtitle }}</h3>
  {% endif %}
  </header>
  <section class="main_body">
    {{ page.blocks.content|raw }}
  </section>
EOT;

        if ($taxonomies) {
            $output .= "\n" . '  <section class="taxonomies">' . "\n";

            foreach ($taxonomies as $taxonomy) {
                $capitalTaxonomy  = ucwords($taxonomy);
                $singularTaxonomy = Inflector::singularize($taxonomy);
                $output .= <<<EOT
    <div class="taxonomy">
        <a href="{{site.url }}/${plural}/{$taxonomy}">${capitalTaxonomy}</a>:
        {% for ${singularTaxonomy} in page.${taxonomy} %}
        <a href="{{ site.url }}/${plural}/${taxonomy}/{{ ${singularTaxonomy} }}">
            {{ ${singularTaxonomy} }}
        </a>{% if not loop.last %}, {% endif %}
        {% endfor %}
      </div>
EOT;
            }

            $output .= "\n" . '  </section>' . "\n";
        }

        $output .= <<<EOT
  <footer>
    <p class="published_date">Published: {{page.date|date('F j, Y')}}</p>
  </footer>
</article>
{% endblock content_wrapper %}
EOT;

        return $output;
    }

    protected function getTaxonomyIndexTemplate($plural, $singular, $taxonomy, $singularTaxonomy)
    {
        $title = ucfirst($taxonomy);

        return <<<EOT
---
use: [${plural}_${taxonomy}]
---
<h1>${title}</h1>
<ul>
    {% for ${singularTaxonomy},${plural} in data.${plural}_${taxonomy} %}
        <li>
            <a href="/${plural}/${taxonomy}/{{ ${singularTaxonomy}|url_encode(true) }}">{{ ${singularTaxonomy} }}</a>
        </li>
    {% endfor %}
</ul>
EOT;
    }

    protected function getTaxonomyViewTemplate($plural, $singular, $taxonomy, $singularTaxonomy)
    {
        $title = ucfirst($plural);

        return <<<EOT
---
generator: [${plural}_${singularTaxonomy}_index, pagination]
pagination:
    provider: page.${singularTaxonomy}_${plural}
    max_per_page: 10
---
<h1>{{ page.${singularTaxonomy}|capitalize }}</h1>
<ul>
    {% for ${singular} in page.pagination.items %}
        <li><a href="{{ ${singular}.url }}">{{ ${singular}.title }}</a></li>
    {% endfor %}
</ul>

<nav>
    {% if page.pagination.previous_page or page.pagination.next_page %}
    {% if page.pagination.previous_page %}
    <a href="{{ site.url }}{{ page.pagination.previous_page.url }}">Newer ${plural}</a>
    {% endif %}
    {% if page.pagination.next_page %}
    <a href="{{ site.url }}{{ page.pagination.next_page.url }}">Older ${plural}</a>
    {% endif %}
    {% endif %}
</nav>
EOT;
    }
}
