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

use sculpin\Sculpin;
use sculpin\bundle\AbstractBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigBundle extends Bundle
{

    const FORMATTER_NAME = 'twig';
    const CONFIG_VIEWS = 'twig.views';
    const CONFIG_EXTENSIONS = 'twig.extensions';

    /**
     * {@inheritdocs}
     */
    public function boot()
    {
        $sculpin = $this->container;
        $viewsPaths = $sculpin->get('sculpin.configuration')->get(self::CONFIG_VIEWS);
        foreach ($viewsPaths as $viewsPath) {
            $sculpin->addExclude($viewsPath.'/**');
        }

        $sculpin->registerFormatter(self::FORMATTER_NAME, new TwigFormatter(
            array_map(function($path) use($sculpin) {
                return $sculpin->get('sculpin.configuration')->getPath('source_dir').'/'.$path;
            }, $viewsPaths),
            $sculpin->get('sculpin.configuration')->get(self::CONFIG_EXTENSIONS)
        ));
    }

}
