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

        $this->assertContains('Hello World', $crawler->filter('h1')->text());
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
        $this->assertContains('Hello World', $pageContentEl->text());
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

        $this->assertContains($expectedHeader, $pageHeaderEl->text());
        $this->assertContains($expectedContent, $pageContentEl->text());

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

        $this->assertContains($expectedContent, $pageContentEl->text());

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

        $this->assertContains($expectedHeader, $pageHeaderEl->text()); // I don't get it. This should be failing.

        $process->stop(0);
    }
}
