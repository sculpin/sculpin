<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\twigBundle;

use sculpin\bundle\AbstractBundle;
use sculpin\Sculpin;

/**
 * Twig Bundle
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class TwigBundle extends AbstractBundle
{
    const FORMATTER_NAME = 'twig';
    const CONFIG_VIEWS = 'twig.views';
    const CONFIG_EXTENSIONS = 'twig.extensions';

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $configuration = $this->configuration;

        $viewsPaths = $configuration->get(self::CONFIG_VIEWS);
        foreach ($viewsPaths as $viewsPath) {
            $this->sculpin->addExclude($viewsPath.'/**');
        }

        $this->sculpin->registerFormatter(self::FORMATTER_NAME, new TwigFormatter(
            array_map(function($path) use($configuration) {
                return $configuration->getPath('source_dir').'/'.$path;
            }, $viewsPaths),
            $configuration->get(self::CONFIG_EXTENSIONS)
        ));
    }
}
