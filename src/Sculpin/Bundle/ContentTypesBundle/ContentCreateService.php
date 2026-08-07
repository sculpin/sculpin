<?php

namespace Sculpin\Bundle\ContentTypesBundle;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Inflector\EnglishInflector;

class ContentCreateService
{
    private const string DIRECTORY_FLAG = '_directory_';

    public function __construct(public ?string $projectDir)
    {
        if (null === $this->projectDir || !is_writable($this->projectDir)) {
            throw new \InvalidArgumentException('Project Dir not found or not writable');
        }
    }

    public function generateBoilerplateManifest(string $plural, string $singular, array $taxonomies = []): array
    {
        $rootDir  = $this->projectDir;
        $manifest = [];

        // ensure the content type storage folder exists
        $storageFolder            = $rootDir . '/source/_' . $plural;
        $manifest[$storageFolder] = self::DIRECTORY_FLAG;

        // content type index template
        $index            = $rootDir . '/source/' . $plural . '.html';
        $manifest[$index] = $this->getIndexTemplate($plural, $singular);

        // ensure the views folder exists
        $storageFolder            = $rootDir . '/source/_views';
        $manifest[$storageFolder] = self::DIRECTORY_FLAG;

        // content type view template
        $index            = $rootDir . '/source/_views/' . $singular . '.html';
        $manifest[$index] = $this->getViewTemplate($plural, $taxonomies);

        foreach ($taxonomies as $taxonomy) {
            $singularTaxonomy = new EnglishInflector()->singularize($taxonomy)[0];
            // content taxonomy index template
            $index            = $rootDir . '/source/' . $plural . '/' . $taxonomy . '.html';
            $manifest[$index] = $this->getTaxonomyIndexTemplate($plural, $taxonomy, $singularTaxonomy);

            // content taxonomy directory
            $storageFolder            = $rootDir . '/source/' . $plural . '/' . $taxonomy;
            $manifest[$storageFolder] = self::DIRECTORY_FLAG;

            // content taxonomy view template(s)
            $index            = $rootDir . '/source/' . $plural . '/' . $taxonomy . '/' . $singularTaxonomy . '.html';
            $manifest[$index] = $this->getTaxonomyViewTemplate($plural, $singular, $singularTaxonomy);
        }

        // create the first content item in the storage folder
        $firstPost            = $rootDir . '/source/_' . $plural . '/first_' . $singular . '.md';
        $manifest[$firstPost] = $this->getFirstItem($singular, $taxonomies);

        return $manifest;
    }

    public function processManifest(array $boilerplateManifest): void
    {
        $fs = new Filesystem();
        foreach ($boilerplateManifest as $filename => $value) {
            // create directory and skip the rest of the loop
            if ($value === self::DIRECTORY_FLAG) {
                $fs->mkdir($filename);
                continue;
            }

            if ($fs->exists($filename)) {
                continue;
            }

            // create file $filename with contents $value
            $fs->dumpFile($filename, $value);
        }
    }

    public function getYamlString(string $type, string $singularType, array $taxonomies = []): string
    {
        $outputMessage = <<<EOT
            {$type}:
                type: path
                path: _{$type}
                singular_name: {$singularType}
                layout: {$singularType}
                enabled: true
                permalink: {$type}/:title
        EOT;

        if ($taxonomies !== []) {
            $outputMessage .= "\n        taxonomies:\n";
            foreach ($taxonomies as $taxonomy) {
                $outputMessage .= sprintf('            - %s%s', $taxonomy, PHP_EOL);
            }
        }

        return $outputMessage . PHP_EOL;
    }

    private function getIndexTemplate(string $plural, string $singular): string
    {
        $title = ucfirst($plural);

        return <<<EOT
        ---
        layout: default
        title: {$title}
        generator: pagination
        pagination:
            provider: data.{$plural}
            max_per_page: 10
        use: [{$plural}]
        ---
        <ul>
            {% for {$singular} in page.pagination.items %}
                <li><a href="{{ {$singular}.url }}">{{ {$singular}.title }}</a></li>
            {% endfor %}
        </ul>

        <nav>
            {% if page.pagination.previous_page or page.pagination.next_page %}
            {% if page.pagination.previous_page %}
            <a href="{{ site.url }}{{ page.pagination.previous_page.url }}">Newer {$title}</a>
            {% endif %}
            {% if page.pagination.next_page %}
            <a href="{{ site.url }}{{ page.pagination.next_page.url }}">Older {$title}</a>
            {% endif %}
            {% endif %}
        </nav>
        EOT;
    }

    private function getViewTemplate(string $plural, array $taxonomies = []): string
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

        if ($taxonomies !== []) {
            $output .= "\n" . '  <section class="taxonomies">' . "\n";

            foreach ($taxonomies as $taxonomy) {
                $capitalTaxonomy  = ucwords((string) $taxonomy);
                $singularTaxonomy = new EnglishInflector()->singularize($taxonomy)[0];
                $output .= <<<EOT
                    <div class="taxonomy">
                        <a href="{{site.url }}/{$plural}/{$taxonomy}">{$capitalTaxonomy}</a>:
                        {% for {$singularTaxonomy} in page.{$taxonomy} %}
                        <a href="{{ site.url }}/{$plural}/{$taxonomy}/{{ {$singularTaxonomy} }}">
                            {{ {$singularTaxonomy} }}
                        </a>{% if not loop.last %}, {% endif %}
                        {% endfor %}
                      </div>
                EOT;
            }

            $output .= "\n" . '  </section>' . "\n";
        }

        return $output . <<<EOT
          <footer>
            <p class="published_date">Published: {{page.date|date('F j, Y')}}</p>
          </footer>
        </article>
        {% endblock content_wrapper %}
        EOT;
    }

    private function getTaxonomyIndexTemplate(
        string $plural,
        string $taxonomy,
        string $singularTaxonomy
    ): string {
        $title = ucfirst($taxonomy);

        // phpcs:disable
        return <<<EOT
        ---
        layout: default
        use: [{$plural}_{$taxonomy}]
        ---
        <h1>{$title}</h1>
        <ul>
            {% for {$singularTaxonomy},{$plural} in data.{$plural}_{$taxonomy} %}
                <li>
                    <a href="/{$plural}/{$taxonomy}/{{ {$singularTaxonomy}|url_encode(true) }}">
                        {{ {$singularTaxonomy} }}
                    </a>
                </li>
            {% endfor %}
        </ul>
        EOT;
        // phpcs:enable
    }

    private function getTaxonomyViewTemplate(
        string $plural,
        string $singular,
        string $singularTaxonomy
    ): string {
        $title = ucfirst($plural);

        return <<<EOT
        ---
        layout: default
        generator: [{$plural}_{$singularTaxonomy}_index, pagination]
        pagination:
            provider: page.{$singularTaxonomy}_{$plural}
            max_per_page: 10
        ---
        <h1>{{ page.{$singularTaxonomy}|capitalize }}</h1>
        <ul>
            {% for {$singular} in page.pagination.items %}
                <li><a href="{{ {$singular}.url }}">{{ {$singular}.title }}</a></li>
            {% endfor %}
        </ul>

        <nav>
            {% if page.pagination.previous_page or page.pagination.next_page %}
            {% if page.pagination.previous_page %}
            <a href="{{ site.url }}{{ page.pagination.previous_page.url }}">Newer {$title}</a>
            {% endif %}
            {% if page.pagination.next_page %}
            <a href="{{ site.url }}{{ page.pagination.next_page.url }}">Older {$title}</a>
            {% endif %}
            {% endif %}
        </nav>
        EOT;
    }

    private function getFirstItem(string $singular, array $taxonomies): string
    {
        $title = ucfirst($singular);

        $taxonomyFrontMatter = implode("\n", array_map(fn ($taxonomy) => "$taxonomy:\n  - example", $taxonomies));

        return <<<EOT
        ---
        layout: default
        title: My First {$title}
        $taxonomyFrontMatter
        ---
        # Welcome to My First {$singular}

        Welcome! This the first {$singular} for {{ site.title }}!

        EOT;
    }
}
