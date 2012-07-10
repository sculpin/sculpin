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
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigBundle extends Bundle
{

    const FORMATTER_NAME = 'twig';
    const CONFIG_VIEWS = 'twig.views';
    const CONFIG_EXTENSIONS = 'twig.extensions';

    protected $configuration;

    /**
     * The Sculpin object.
     *
     * @var Sculpin
     */
    protected $sculpin;

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        // Extract objects that are required from the container.
        $this->configuration = $container->get('sculpin.configuration');
        $this->sculpin = $container->get('sculpin');
    }

    /**
     * {@inheritdocs}
     */
    public function boot()
    {
        $config = $this->configuration;
        $viewsPaths = $config->get(self::CONFIG_VIEWS);
        foreach ($viewsPaths as $viewsPath) {
            $this->sculpin->addExclude($viewsPath.'/**');
        }

        $this->sculpin->registerFormatter(self::FORMATTER_NAME, new TwigFormatter(
            array_map(function($path) use($config) {
                return $config->getPath('source_dir').'/'.$path;
            }, $viewsPaths),
            $config->get(self::CONFIG_EXTENSIONS)
        ));
    }

}
