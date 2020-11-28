<?php

declare(strict_types=1);

namespace Sculpin\Bundle\TwigBundle;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class WebpackEncoreHelper extends AbstractExtension implements GlobalsInterface
{
    protected $sourceDir;
    protected $manifest;

    public function __construct(string $sourceDir, ?string $manifest)
    {
        $this->sourceDir = $sourceDir;
        $this->manifest = $manifest;
    }

    public function getGlobals(): array
    {
        $manifestContents = $this->getManifestContents();

        if (!$manifestContents) {
            return [
                'webpack_manifest' =>
                    [
                        'error' => 'Please configure the `sculpin_twig.webpack_manifest` variable '
                            . 'in your app/config/sculpin_kernel.yml file. For example:' . "\n\n"
                            . 'sculpin_twig:' . "\n"
                            . '    webpack_manifest: build/manifest.json'
                    ]
            ];
        }

        $manifest = json_decode($manifestContents, true);

        return [
            'webpack_manifest' => $manifest,
        ];
    }

    private function getManifestContents(): string
    {
        $path = $this->sourceDir . DIRECTORY_SEPARATOR . $this->manifest;

        if (!file_exists($path) || !is_readable($path) || !is_file($path)) {
            return '';
        }

        return file_get_contents($path);
    }
}
