<?php

namespace Sculpin\Tests\Functional;

class GenerateFromMarkdownTest extends FunctionalTestCase
{
    /** @test */
    public function shouldGenerateAnHtmlFileFromMarkdown()
    {
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/hello_world.md');

        $this->executeSculpin('generate');

        $this->assertProjectHasGeneratedFile('/hello_world/index.html');
    }

    /** @test */
    public function shouldGenerateHtmlContentFromMarkdown()
    {
        $this->copyFixtureToProject(__DIR__ . '/Fixture/source/hello_world.md', '/source/hello_world.md');

        $this->executeSculpin('generate');

        $crawler = $this->crawlGeneratedProjectFile('/hello_world/index.html');

        $this->assertContains('Hello World', $crawler->filter('h1')->text());
    }

    /** @test */
    public function shouldGenerateIntoNestedDirectories()
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
}
