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

use sculpin\event\SourceFilesChangedEvent;

use sculpin\bundle\AbstractBundle;

class TwigBundle extends AbstractBundle
{

    const FORMATTER_NAME = 'twig';
    const CONFIG_VIEWS = 'twig.views';
    const CONFIG_EXTENSIONS = 'twig.extensions';
    
    /**
     * (non-PHPdoc)
     * @see sculpin\bundle.AbstractBundle::configureBundle()
     */
    public function configureBundle(Sculpin $sculpin)
    {
        $viewsPaths = $sculpin->configuration()->get(self::CONFIG_VIEWS);
        if ($sculpin->sourceIsProjectRoot()) {
            foreach ($viewsPaths as $viewsPath) {
                $sculpin->exclude($viewsPath.'/**');
            }
        }
        $sculpin->registerFormatter(self::FORMATTER_NAME, new TwigFormatter(
            array_map(function($path) use($sculpin) { return $sculpin->configuration()->getPath('source').'/'.$path; }, $viewsPaths),
            $sculpin->configuration()->get(self::CONFIG_EXTENSIONS)
        ));
    }

}
