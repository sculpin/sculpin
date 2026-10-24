<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional;

final class ContentTypeUseConfigurationTest extends FunctionalTestCase
{
    /** @test */
    public function shouldInjectDataProviderFromContentTypeConfiguration(): void
    {
        $this->addProjectDirectory('/source/_posts');

        // Configure posts content type with use option
        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yml',
            <<<EOT
            sculpin_content_types:
              posts:
                permalink: blog/:basename
                use:
                  - posts
            EOT
        );

        // Create a post
        $this->writeToProjectFile(
            '/source/_posts/first-post.md',
            <<<EOT
            ---
            title: First Post
            ---
            This is the first post.
            EOT
        );

        // Create a second post
        $this->writeToProjectFile(
            '/source/_posts/second-post.md',
            <<<EOT
            ---
            title: Second Post
            ---
            This is the second post.
            EOT
        );

        // Create a post that references other posts (without front matter use:)
        $this->writeToProjectFile(
            '/source/_posts/third-post.md',
            <<<EOT
            ---
            title: Third Post
            ---
            Other posts: {% for post in data.posts %}{{ post.title }}{% if not loop.last %}, {% endif %}{% endfor %}
            EOT
        );

        $this->executeSculpin(['generate']);

        // The third post should have access to data.posts via content type config
        $this->assertProjectHasGeneratedFile('/blog/third-post/index.html');
        $this->assertGeneratedFileHasContent(
            '/blog/third-post/index.html',
            'First Post'
        );
        $this->assertGeneratedFileHasContent(
            '/blog/third-post/index.html',
            'Second Post'
        );
    }

    /** @test */
    public function shouldSupportStringUseConfiguration(): void
    {
        $this->addProjectDirectory('/source/_posts');

        // Configure with string instead of array
        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yml',
            <<<EOT
            sculpin_content_types:
              posts:
                permalink: blog/:basename
                use: posts
            EOT
        );

        // Create posts
        $this->writeToProjectFile(
            '/source/_posts/first-post.md',
            <<<EOT
            ---
            title: First Post
            ---
            This is the first post.
            EOT
        );

        $this->writeToProjectFile(
            '/source/_posts/second-post.md',
            <<<EOT
            ---
            title: Second Post
            ---
            Count: {{ data.posts|length }}
            EOT
        );

        $this->executeSculpin(['generate']);

        $this->assertProjectHasGeneratedFile('/blog/second-post/index.html');
        $this->assertGeneratedFileHasContent(
            '/blog/second-post/index.html',
            'Count: 2'
        );
    }

    /** @test */
    public function shouldWorkWithoutUseConfiguration(): void
    {
        $this->addProjectDirectory('/source/_posts');

        // Configure without use option (existing behaviour)
        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yml',
            <<<EOT
            sculpin_content_types:
              posts:
                permalink: blog/:basename
            EOT
        );

        // Create a post that tries to access data.posts without use
        $this->writeToProjectFile(
            '/source/_posts/no-data.md',
            <<<EOT
            ---
            title: No Data Post
            ---
            {% if data.posts is defined %}Has posts{% else %}No posts{% endif %}
            EOT
        );

        $this->executeSculpin(['generate']);

        $this->assertProjectHasGeneratedFile('/blog/no-data/index.html');
        $this->assertGeneratedFileHasContent(
            '/blog/no-data/index.html',
            'No posts'
        );
    }
}
