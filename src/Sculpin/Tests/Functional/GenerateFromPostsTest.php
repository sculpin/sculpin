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

    /**
     * @return void
     */
    private function prepareTagsSupport(): void
    {
        $this->addProjectDirectory('/source/blog/tags');
        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yml',
            <<<EOT
            sculpin_content_types:
              posts:
                permalink: blog/:basename
            EOT
        );
        $this->writeToProjectFile(
            '/source/blog/tags.html',
            <<<EOT
            ---
            layout: default
            title: Tags
            use:
                - posts_tags
            ---
            <h2>Tags</h2>
            <ul>
            {% for tag,posts in data.posts_tags %}
            <li>
                <a href="{{ site.url }}/blog/tags/{{ tag }}">
                {{ tag|capitalize }}<span>{{ posts|length}} posts</span>
                </a>
            </li>
            {% endfor %}
            </ul>
            EOT
        );
        $this->writeToProjectFile(
            '/source/blog/tags/tag.html',
            <<<EOT
            ---
            layout: default
            title: Tag Archive
            generator: [posts_tag_index, pagination]
            pagination:
                provider: page.tagged_posts
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
