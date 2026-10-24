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

namespace Sculpin\Bundle\SculpinBundle\Command;

use Sculpin\Bundle\ContentTypesBundle\ContentCreateService;
use Sculpin\Bundle\SculpinBundle\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Initialize default website configuration and structure.
 */
final class InitCommand extends AbstractCommand
{
    public const int PROJECT_FOLDER_NOT_EMPTY = 101;
    public const string DEFAULT_SUBTITLE = 'A Static Site Powered By Sculpin';
    public const string DEFAULT_TITLE    = 'My Sculpin Site';

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $prefix = $this->isStandaloneSculpin() ? '' : 'sculpin:';

        $this
            ->setName($prefix . 'init')
            ->setDescription('Initialize a default site configuration.')
            ->setDefinition([
                new InputOption(
                    'title',
                    't',
                    InputOption::VALUE_REQUIRED,
                    'Title for your new website',
                ),
                new InputOption(
                    'subtitle',
                    's',
                    InputOption::VALUE_REQUIRED,
                    'Sub-title for your new website',
                    self::DEFAULT_SUBTITLE
                ),
                new InputOption(
                    'posts',
                    'p',
                    InputOption::VALUE_NONE,
                    'Configure the website as a blog'
                ),
                new InputOption(
                    'no-posts',
                    null,
                    InputOption::VALUE_NONE,
                    'Disable blog posts (default)'
                ),
            ])
            ->setHelp(<<<EOT
            The <info>init</info> command initializes a default site configuration.

            If title/subtitle are not provided, the command will ask the user for input.

            Once the site has been created, the `content:create` command can be used
            to create custom Content Types, such as Blog Posts.
            EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->getApplication();
        if ($application instanceof Application) {
            foreach ($application->getMissingSculpinBundlesMessages() as $message) {
                $output->writeln($message);
            }
        }

        $title    = $input->getOption('title');
        $subTitle = $input->getOption('subtitle');
        $enablePosts = $input->getOption('posts');
        $disablePosts = $input->getOption('no-posts');

        $questionHelper = new QuestionHelper();

        $question = new Question('Title for your website:', self::DEFAULT_TITLE);
        $title ??= $questionHelper->ask($input, $output, $question);

        $question = new Question('Sub-heading for your website:', self::DEFAULT_SUBTITLE);
        $subTitle ??= $questionHelper->ask($input, $output, $question);

        $posts = false;
        if (!$enablePosts && !$disablePosts) {
            $question = new ChoiceQuestion(
                'Will your website have blog posts?',
                ['y' => 'yes', 'n' => 'no (default)'],
                'n'
            );

            $posts = match ($questionHelper->ask($input, $output, $question)) {
                'y' => true,
                default => false,
            };
        } elseif ($enablePosts) {
            $posts = true;
        }

        $projectDir = $this->getContainer()->getParameter('sculpin.project_dir');
        $output->writeln('Project Directory: <info>' . $projectDir . '</info>');

        $output->writeln('Initializing <info>./app</info> and <info>./source</info> for Sculpin' . "\n");

        // Actions:

        // 1. Ensure we're operating on a clean slate
        if (!$this->ensureCleanSlate($projectDir, $output)) {
            $output->writeln('<error>This command can only be run in an uninitialized folder.</error>');

            return self::PROJECT_FOLDER_NOT_EMPTY;
        }

        // 2. Create default Kernel
        $this->createDefaultKernel($projectDir);

        // 3. Create default Site config files
        $this->createSiteKernelFile($projectDir, $posts);
        $this->createSiteConfigFile($projectDir, $title, $subTitle);

        // 4. Create source folder (with or without posts)
        $this->createSourceFolder($projectDir, $posts);

        $output->writeln('<info>Success!</info>');
        $output->writeln('Run "sculpin generate --watch --server --editor" to see your static site in action.');
        $output->writeln('');

        if (!$posts) {
            $output->writeln(
                'Or, use the `sculpin content:create` command to create a custom Content Type, such as Blog Posts.'
            );
        }

        return self::SUCCESS;
    }

    private function ensureCleanSlate(string $projectDir, OutputInterface $output): bool
    {
        $fs = new Filesystem();
        if ($fs->exists($projectDir . '/app')) {
            $output->writeln('<info>/app folder exists.</info>');

            return false;
        }

        if ($fs->exists($projectDir . '/source')) {
            $output->writeln('<info>/source folder exists.</info>');

            return false;
        }

        return true;
    }

    private function createDefaultKernel(string $projectDir): void
    {
        $contents = <<<EOT
        <?php

        class SculpinKernel extends \Sculpin\Bundle\SculpinBundle\HttpKernel\AbstractKernel
        {
            protected function getAdditionalSculpinBundles(): array
            {
                return [
        //            App\Bundle\ExampleBundle\AppExampleBundle:class,
                ];
            }
        }

        EOT;
        $this->createFile($projectDir . '/app/SculpinKernel.php', $contents);
    }

    private function createSiteKernelFile(string $projectDir, bool $posts = false): void
    {
        if ($posts) {
            $contents = <<<EOT
            sculpin_content_types:
                posts:
                    type: path
                    path: _posts
                    permalink: pretty
                    taxonomies:
                        - tags
                        - categories
            EOT;
        } else {
            $contents = <<<EOT
            sculpin_content_types:
                posts:
                  enabled: false
            EOT;
        }

        $this->createFile($projectDir . '/app/config/sculpin_kernel.yml', $contents);
    }

    private function createSiteConfigFile(
        string $projectDir,
        string $title,
        string $subTitle
    ): void {
        $contents = <<<EOT
        # These values will be available in your markdown and HTML twig templates, in the `site` object.
        # Example: `{{ site.title }}`
        title: "{$title}"
        subtitle: "{$subTitle}"
        google_analytics_tracking_id: ''
        url: ''

        EOT;
        $this->createFile($projectDir . '/app/config/sculpin_site.yml', $contents);
    }

    private function createSourceFolder(string $projectDir, bool $posts = false): void
    {
        $fs = new Filesystem();

        if (!$posts) {
            // Sites without posts get a bare-bones Index
            $fs->dumpFile(
                $projectDir . '/source/index.md',
                <<<EOT
                ---
                layout: default
                ---

                <h1>Welcome to {{site.title}}</h1>

                EOT
            );
        } else {
            $fs->dumpFile(
                $projectDir . '/source/index.md',
                <<<EOT
                ---
                layout: default
                generator: pagination
                pagination:
                    max_per_page: 6
                use:
                    - posts
                ---

                <h1>Welcome to {{site.title}}</h1>

                <h2>Recent Posts</h2>
                {% for post in page.pagination.items %}
                    <article>
                        <header>
                            <div><h2><a href="{{ site.url }}{{ post.url }}">{{ post.title }}</a></h2></div>
                            <div>{{post.date|date("F j, Y")}}</div>
                        </header>
                        <div>
                                {# Split the post into an array using explode().
                                   Because we provide a length of 2, the "rest"
                                   of the post will be stored in the second array
                                   element, even if there are multiple breakpoints. #}
                                {% set break_array =
                                    post.blocks.content|split('<!-- break -->', 2) %}

                                {# Output the first element of the array in raw mode #}
                                {{ break_array[0]|raw }}

                                {# Detect if there is more to the post. If the post
                                   was only one array element with no breakpoints,
                                   it would all have shown up. This Read More link should
                                   only show up if there is overflow to the post. #}
                                {% if break_array|length > 1 %}
                                    <div><a href="{{ site.url }}{{ post.url }}">
                                        Read more of this post »
                                    </a></div>
                                {% endif %}
                        </div>
                        {% if post.meta.tags %}
                        <div>
                            <small>Tags: {% for tag in post.meta.tags %}
                            <a href="{{ site.url }}/posts/tags/{{ tag }}">{{ tag }}</a>
                            {% endfor %}
                            </small>
                        </div>
                        {% endif %}
                    </article>
                {% else %}
                <p>There no recent posts.</p>
                {% endfor %}

                {% import 'macros.twig' as util %}
                {{ util.pagination(page, site.url, '« Newer Posts', 'Older Posts »') }}

                EOT
            );
        }

        $fs->dumpFile(
            $projectDir . '/source/_views/default.html',
            <<<EOT
            <html>
            <head><title>{{site.title}}</title></head>
            <body>
            {% block content_wrapper %}{% block content '' %}{% endblock content_wrapper %}
            </body>
            </html>

            EOT
        );

        $fs->dumpFile(
            $projectDir . '/source/_includes/macros.twig',
            <<<EOT
            {% macro pagination(page, site_url, previous = 'Older', next = 'Newer') %}
                <div class="pagination">
                    {% if page.pagination.previous_page or page.pagination.next_page %}
                        {% if page.pagination.previous_page %}
                            <a href="{{ site_url }}{{ page.pagination.previous_page.url }}" title="{{ previous }}">
                                <span>{{ previous|raw }}</span>
                            </a>
                        {% endif %}
                        {% if page.pagination.next_page %}
                            <a href="{{ site_url }}{{ page.pagination.next_page.url }}" title="{{ next }}">
                                <span>{{ next|raw}}</span>
                            </a>
                        {% endif %}
                    {% endif %}
                </div>
            {% endmacro %}

            {% macro breadcrumb(items) %}
                <div class="breadcrumb">
                    {% for url, label in items %}
                        {% if not loop.first %}<span>&raquo;</span>{% endif %}
                    <a href="{{site.url}}{{url}}">{{ label }}</a>
                    {% endfor %}
                </div>
            {% endmacro %}

            EOT
        );

        if ($posts) {
            $contentService = new ContentCreateService($projectDir);

            $manifest = $contentService->generateBoilerplateManifest(
                'posts',
                'post',
                ['tags', 'categories'],
            );

            $contentService->processManifest($manifest);
        }
    }

    private function createFile(string $path, string $contents): void
    {
        $fs = new Filesystem();
        $fs->dumpFile($path, $contents);
    }
}
