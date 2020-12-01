<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional;

class GenerateFromMarkdownTest extends FunctionalTestCase
{
    /** @test */
    public function shouldGenerateAnHtmlFileFromMarkdown(): void
    {
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/hello_world.md');

        $this->executeSculpin('generate');

        $this->assertProjectHasGeneratedFile('/hello_world/index.html');
    }

    /** @test */
    public function shouldGenerateHtmlContentFromMarkdown(): void
    {
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/hello_world.md');

        $this->executeSculpin('generate');

        $crawler = $this->crawlGeneratedProjectFile('/hello_world/index.html');

        $this->assertStringContainsString('Hello World', $crawler->filter('h1')->text());
    }

    /** @test */
    public function shouldGenerateIntoNestedDirectories(): void
    {
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/hello/world.md');

        $this->executeSculpin('generate');

        $this->assertProjectHasGeneratedFile('/hello/world/index.html');
    }

    /** @test */
    public function shouldGenerateHtmlUsingALayout()
    {
        $this->addProjectFile('/source/_layouts/my_layout.html.twig', <<<EOT
<body>
	<div class="page-content">{% block content %}{% endblock content %}</div>
</body>
EOT
        );

        $this->addProjectFile('/source/my_page_with_layout.md', <<<EOT
---
layout: my_layout.html.twig
---
Hello World
EOT
        );

        $this->executeSculpin('generate');

        $crawler = $this->crawlGeneratedProjectFile('/my_page_with_layout/index.html');

        $pageContentEl = $crawler->filter('.page-content');
        $this->assertEquals(
            1,
            $pageContentEl->count(),
            "Expected generated file to have a single .page-content element."
        );
        $this->assertStringContainsString('Hello World', $pageContentEl->text());
    }

    /** @test */
    public function shouldRefreshGeneratedHtmlAfterFilesystemChange(): void
    {
        $layoutFile    = '/source/_layouts/my_layout.html.twig';
        $pageFile      = '/source/my_page_with_layout.md';
        $pageGenerated = '/my_page_with_layout/index.html';

        $expectedHeader  = 'ORIGINAL_HEADER';
        $expectedContent = 'Hello World';

        $layoutContent = <<<EOT
<body>
    <h1 class="header">{$expectedHeader}</h1>
	<div class="page-content">{% block content %}{% endblock content %}</div>
</body>
EOT;

        $pageContent = <<<EOT
---
layout: my_layout.html.twig
---
{$expectedContent}
EOT;

        $this->addProjectFile($layoutFile, $layoutContent);
        $this->addProjectFile($pageFile, $pageContent);

        // start our async sculpin watcher/server
        $process = $this->executeSculpinAsync('generate --watch');

        sleep(1); // wait until our file exists
        $crawler = $this->crawlGeneratedProjectFile($pageGenerated);

        $pageContentEl = $crawler->filter('.page-content');
        $this->assertEquals(
            1,
            $pageContentEl->count(),
            "Expected generated file to have a single .page-content element."
        );

        $pageHeaderEl = $crawler->filter('.header');
        $this->assertEquals(
            1,
            $pageHeaderEl->count(),
            "Expected generated file to have a single .header element."
        );

        $this->assertStringContainsString($expectedHeader, $pageHeaderEl->text());
        $this->assertStringContainsString($expectedContent, $pageContentEl->text());

        // update the content
        $originalHeader  = $expectedHeader;
        $originalContent = $expectedContent;

        $expectedHeader  = 'FRESH HEADER';
        $expectedContent = 'HELLO WORLD!';

        $layoutContent = str_replace($originalHeader, $expectedHeader, $layoutContent);
        $pageContent   = str_replace($originalContent, $expectedContent, $pageContent);

        // test that page content refreshes properly
        $this->addProjectFile($pageFile, $pageContent);

        sleep(2);
        $crawler = $this->crawlGeneratedProjectFile($pageGenerated);

        $pageContentEl = $crawler->filter('.page-content');
        $this->assertEquals(
            1,
            $pageContentEl->count(),
            "Expected generated file to have a single .page-content element."
        );

        $this->assertStringContainsString($expectedContent, $pageContentEl->text());

        // test that layouts/views refresh properly
        $this->addProjectFile($layoutFile, $layoutContent);

        sleep(2);
        $crawler = $this->crawlGeneratedProjectFile($pageGenerated);

        $pageHeaderEl = $crawler->filter('.header');
        $this->assertEquals(
            1,
            $pageHeaderEl->count(),
            "Expected generated file to have a single .header element."
        );

        $this->assertStringContainsString(
            $expectedHeader,
            $pageHeaderEl->text()
        );

        $process->stop(0);
    }

    /** @test */
    public function shouldPassThruFilesWithNoExtension(): void
    {
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/hello_world');
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/hello_world2');

        $this->executeSculpin('generate');

        $this->assertProjectHasGeneratedFile('/hello_world');
        $this->assertProjectHasGeneratedFile('/hello_world2');

        $this->assertGeneratedFileHasContent('/hello_world', '# Hello World');
        $this->assertGeneratedFileHasContent('/hello_world2', '# Hello World');
    }

    /** @test */
    public function shouldSkipContentTypeFilesWithNoExtension(): void
    {
        $this->addProjectDirectory(__DIR__ . '/Fixture/source/_posts');
        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yml',
            <<<EOF
sculpin_content_types:
  posts:
    permalink: blog/:basename
EOF
        );

        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/_posts/hello_world');
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/_posts/hello_world2');
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/_posts/hello_world3.md');

        $this->executeSculpin('generate');

        $actualOutput = $this->executeOutput;
        $this->assertStringContainsString(
            'Skipping empty or unknown file: _posts/hello_world' . PHP_EOL,
            $actualOutput
        );
        $this->assertStringContainsString('Skipping empty or unknown file: _posts/hello_world2', $actualOutput);
        $this->assertStringNotContainsString('Skipping empty or unknown file: _posts/hello_world3.md', $actualOutput);

        $this->assertProjectLacksFile('/output_test/_posts/hello_world');
        $this->assertProjectLacksFile('/output_test/_posts/hello_world2');
        $this->assertProjectHasGeneratedFile('/blog/hello_world3/index.html');

        $this->assertGeneratedFileHasContent(
            '/blog/hello_world3/index.html',
            '<h1 id="hello-world">Hello World</h1>'
        );
    }

    /** @test */
    public function shouldSkipHiddenFilesSilently(): void
    {
        $this->addProjectDirectory(__DIR__ . '/Fixture/source/_posts');
        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yml',
            <<<EOF
sculpin_content_types:
  posts:
    permalink: blog/:basename
EOF
        );

        $this->addProjectFile('/source/_posts/.DS_Store');
        $this->addProjectFile('/source/_posts/.hello_world2.swp');
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/_posts/hello_world3.md');

        $this->executeSculpin('generate');

        $actualOutput = $this->executeOutput;
        $this->assertStringNotContainsString('.DS_Store', $actualOutput);
        $this->assertStringNotContainsString('.hello_world2.swp', $actualOutput);
        $this->assertStringNotContainsString('Skipping empty or unknown file:', $actualOutput);

        $this->assertProjectLacksFile('/output_test/_posts/.DS_Store');
        $this->assertProjectLacksFile('/output_test/_posts/.hello_world2.swp');
        $this->assertProjectHasGeneratedFile('/blog/hello_world3/index.html');

        $this->assertGeneratedFileHasContent(
            '/blog/hello_world3/index.html',
            '<h1 id="hello-world">Hello World</h1>'
        );
    }
}
