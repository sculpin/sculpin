<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional;

final class PermalinkNoneTest extends FunctionalTestCase
{
    /** @test */
    public function shouldNotGenerateFilesForContentTypeItems(): void
    {
        $this->addProjectDirectory('/source/_testimonials');

        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yml',
            <<<EOT
            sculpin_content_types:
              testimonials:
                layout: default
            EOT
        );

        $this->writeToProjectFile(
            '/source/_testimonials/jane-smith.md',
            <<<EOT
            ---
            title: Jane Smith
            ---
            Jane's testimonial.
            EOT
        );

        $this->writeToProjectFile(
            '/source/_testimonials/john-doe.md',
            <<<EOT
            ---
            title: John Doe
            ---
            John's testimonial.
            EOT
        );

        $this->writeToProjectFile(
            '/source/index.html',
            <<<EOT
            ---
            layout: default
            title: Home
            use:
                - testimonials
            ---
            Testimonials:
            {% for t in data.testimonials %}{{ t.title }}{% if not loop.last %}, {% endif %}{% endfor %}
            EOT
        );

        $this->executeSculpin(['generate']);

        // Content type items are still collected for use in other pages...
        $this->assertProjectHasGeneratedFile('/index.html');
        $this->assertGeneratedFileHasContent('/index.html', 'Jane Smith, John Doe');

        // ...but their items should not be written to the output directory.
        $this->assertProjectLacksFile('/output_test/_testimonials/jane-smith.md');
        $this->assertProjectLacksFile('/output_test/_testimonials/john-doe.md');
    }

    /** @test */
    public function shouldNotGenerateFilesForPagesWithPermalinkNone(): void
    {
        $this->writeToProjectFile(
            '/source/hidden-page.md',
            <<<EOT
            ---
            title: Hidden Page
            permalink: none
            ---
            This page should not be generated.
            EOT
        );

        $this->executeSculpin(['generate']);

        $this->assertProjectLacksFile('/output_test/hidden-page.md');
        $this->assertProjectLacksFile('/output_test/hidden-page.html');
        $this->assertProjectLacksFile('/output_test/hidden-page/index.html');
    }

    /** @test */
    public function shouldKeepUrlsAssignedForListedSourcesWithPermalinkNone(): void
    {
        // e.g. a syndicated post: no page of its own, but still listed on
        // other pages, where templates render `post.external_url|default(post.url)`.
        // A source without an assigned permalink would crash that lookup.
        $this->addProjectDirectory('/source/_posts');

        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yml',
            <<<EOT
            sculpin_content_types:
              posts:
                use:
                  - posts
            EOT
        );

        $this->writeToProjectFile(
            '/source/_posts/syndicated.md',
            <<<EOT
            ---
            title: Syndicated Post
            permalink: none
            external_url: https://example.com/elsewhere
            ---
            This post lives elsewhere.
            EOT
        );

        $this->writeToProjectFile(
            '/source/index.html',
            <<<EOT
            ---
            layout: default
            title: Home
            use:
                - posts
            ---
            {% for post in data.posts %}
            {{ post.external_url|default(post.url) }}
            {% endfor %}
            EOT
        );

        $this->executeSculpin(['generate']);

        $this->assertProjectHasGeneratedFile('/index.html');
        $this->assertGeneratedFileHasContent('/index.html', 'https://example.com/elsewhere');

        $this->assertProjectLacksFile('/output_test/_posts/syndicated.md');
        $this->assertProjectLacksFile('/output_test/blog/syndicated/index.html');
    }
}
