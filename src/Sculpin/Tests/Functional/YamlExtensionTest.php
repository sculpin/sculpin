<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional;

final class YamlExtensionTest extends FunctionalTestCase
{
    /** @test */
    public function shouldUseKernelAndSiteConfigInYamlFormat(): void
    {
        // The default test project scaffolds empty .yml config files;
        // remove them so the .yaml files are not shadowed.
        static::$fs->remove([
            static::projectDir().'/app/config/sculpin_kernel.yml',
            static::projectDir().'/app/config/sculpin_site.yml',
        ]);

        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yaml',
            'sculpin_content_types:'."\n"
            .'    testimonials:'."\n"
            .'        layout: default'."\n"
        );

        $this->writeToProjectFile(
            '/app/config/sculpin_site.yaml',
            'title: "YAML Project"'
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

        $this->copyFixtureToProject(__DIR__.'/Fixture/source/hello_world.md', '/source/hello_world.md');

        $this->executeSculpin(['generate']);

        // Kernel config from sculpin_kernel.yaml was applied.
        $this->assertGeneratedFileHasContent('/index.html', 'Testimonials:');
        $this->assertProjectHasGeneratedFile('/index.html');

        // Site config from sculpin_site.yaml was applied.
        $this->assertGeneratedFileHasContent('/hello_world/index.html', 'title: "YAML Project"');
    }

    /** @test */
    public function shouldUseYamlSiteConfigWhenYmlIsMissing(): void
    {
        // The default test project scaffolds an empty .yml site config;
        // remove it so the .yaml file is not shadowed.
        static::$fs->remove(static::projectDir().'/app/config/sculpin_site.yml');

        $this->writeToProjectFile(
            '/app/config/sculpin_site.yaml',
            'title: "YAML Project"'
        );

        $this->copyFixtureToProject(__DIR__.'/Fixture/source/hello_world.md', '/source/hello_world.md');

        $this->executeSculpin(['generate']);

        $this->assertGeneratedFileHasContent('/hello_world/index.html', 'title: "YAML Project"');
    }

    /** @test */
    public function shouldDetectThemeWithYamlMetadataFile(): void
    {
        $this->writeToProjectFile(
            '/app/config/sculpin_kernel.yml',
            'sculpin_theme:'."\n"
            .'    theme: test/test-theme'."\n"
        );

        $this->addProjectDirectory('/source/themes/test/test-theme');
        $this->writeToProjectFile('/source/themes/test/test-theme/theme.yaml', 'title: "Test Theme"');

        $this->executeSculpin(['theme:list']);

        $this->assertStringContainsString('test/test-theme *', $this->executeOutput);
    }

    /** @test */
    public function shouldPreferYmlOverYamlWhenBothExist(): void
    {
        // The default .yml site config exists from the test project scaffold;
        // give it a distinguishing value.
        $this->writeToProjectFile('/app/config/sculpin_site.yml', 'title: "YML Project"');
        $this->writeToProjectFile('/app/config/sculpin_site.yaml', 'title: "YAML Project"');

        $this->copyFixtureToProject(__DIR__.'/Fixture/source/hello_world.md', '/source/hello_world.md');

        $this->executeSculpin(['generate']);

        $this->assertGeneratedFileHasContent('/hello_world/index.html', 'title: "YML Project"');
    }
}

