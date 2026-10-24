<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional;

final class GenerateFromPostsTest extends FunctionalTestCase
{
    /** @test */
    public function shouldGenerateAnHtmlFileFromEmptyPost(): void
    {
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/blog_index.html', '/source/index.html');
        $this->addProjectDirectory('/source/_posts');

        $this->executeSculpin(['generate']);

        $this->assertProjectHasGeneratedFile('/index.html');
    }

    /** @test */
    public function shouldConvertStringTagToArrayOnDrafts(): void
    {
        $this->addProjectDirectory('/source/_posts');

        // Create files related to indexing content by tags
        $this->prepareTagsSupport();

        // Add some initial posts
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/_posts/hello_world3.md');
        $this->copyFixtureToProject(
            __DIR__ . '/Fixture/source/hello_world_draft_tagged.md',
            '/source/_posts/tagged_world.md'
        );

        $this->executeSculpin(['generate']);

        $this->assertProjectHasGeneratedFile('/blog/tagged_world/index.html');
        $this->assertGeneratedFileHasContent(
            '/blog/tagged_world/index.html',
            'tags: [opinion, drafts]'
        );

        $this->assertProjectHasGeneratedFile('/blog/tags/index.html');
        $this->assertGeneratedFileHasContent(
            '/blog/tags/index.html',
            'Opinion<span>1 post'
        );
        $this->assertGeneratedFileHasContent(
            '/blog/tags/index.html',
            'Drafts<span>1 post'
        );

        $this->assertProjectHasGeneratedFile('/blog/tags/drafts/index.html');
        $this->assertGeneratedFileHasContent(
            '/blog/tags/drafts/index.html',
            'Tagged World</a>        [opinion, drafts]'
        );
        $this->assertProjectHasGeneratedFile('/blog/tags/opinion/index.html');
        $this->assertGeneratedFileHasContent(
            '/blog/tags/opinion/index.html',
            'Tagged World</a>        [opinion, drafts]'
        );
    }

    /** @test */
    public function shouldProperlyHandleContentTypesAndKeepThemSeparated(): void
    {
        $this->addProjectDirectory('/source/_posts');
        $this->addProjectDirectory('/source/_posts2');

        // Manually rewrite the kernel yaml to create two post types
        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yml',
            <<<EOT
            sculpin_content_types:
              posts:
                permalink: post/:basename
              posts2:
                permalink: post2/:basename
            EOT
        );

        // Add some initial posts
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/_posts/hello_world3.md');
        $this->copyFixtureToProject(
            __DIR__ . '/Fixture/source/hello_world_draft_tagged.md',
            '/source/_posts/tagged_world.md'
        );
        $this->copyFixtureToProject(
            __DIR__ . '/Fixture/source/hello_world_textile_tagged.textile',
            '/source/_posts2/tagged_hello.md'
        );

        $this->executeSculpin(['generate']);

        $this->assertProjectHasGeneratedFile('/post/tagged_world/index.html');
        $this->assertGeneratedFileHasContent(
            '/post/tagged_world/index.html',
            'Hello Tagged World'
        );

        $this->assertProjectLacksFile('/output_test/post/tagged_hello/index.html');
        $this->assertProjectHasGeneratedFile('/post2/tagged_hello/index.html');
        $this->assertGeneratedFileHasContent(
            '/post2/tagged_hello/index.html',
            'Aenean id lacinia tellus'
        );
    }

    private function prepareTagsSupport(string $contentTypePlural = 'posts', string $contentTypeSingular = 'blog'): void
    {
        $this->addProjectDirectory('/source/' . $contentTypeSingular . '/tags');
        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yml',
            <<<EOT
            sculpin_content_types:
              $contentTypePlural:
                permalink: $contentTypeSingular/:basename
            EOT
        );
        $this->writeToProjectFile(
            '/source/' . $contentTypeSingular . '/tags.html',
            <<<EOT
            ---
            layout: default
            title: Tags
            use:
                - {$contentTypePlural}_tags
            ---
            <h2>Tags</h2>
            <ul>
            {% for tag,$contentTypePlural in data.{$contentTypePlural}_tags %}
            <li>
                <a href="{{ site.url }}/{$contentTypeSingular}/tags/{{ tag }}">
                {{ tag|capitalize }}<span>{{ $contentTypePlural|length}} $contentTypePlural</span>
                </a>
            </li>
            {% endfor %}
            </ul>
            EOT
        );
        $this->writeToProjectFile(
            '/source/' . $contentTypeSingular . '/tags/tag.html',
            <<<EOT
            ---
            layout: default
            title: Tag Archive
            generator: [{$contentTypePlural}_tag_index, pagination]
            pagination:
                provider: page.tagged_{$contentTypePlural}
            ---
            <h2>Tag: <span>"{{ page.tag|capitalize }}"</span></h2>
            <ul>
            {% for post in page.pagination.items %}
                <li>
                    <a href="{{ post.url }}">{{ post.title }}</a>
                    {%- if post.meta.tags %}
                    [{% for tag in post.meta.tags %}{{ tag }}{% if not loop.last %}, {%endif%}{% endfor %}]
                    {% endif %}
                </li>
            {% endfor %}
            </ul>
            EOT
        );
    }
}
