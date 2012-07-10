<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\twigLiquidCompatibilityBundle;

use sculpin\bundle\twigLiquidCompatibilityBundle\tokenParser\AssignTokenParser;

use sculpin\bundle\twigLiquidCompatibilityBundle\tokenParser\CaptureTokenParser;

use sculpin\bundle\twigBundle\TwigFormatter;

use sculpin\formatter\IFormatter;

use sculpin\bundle\twigBundle\TwigBundle;

use sculpin\Sculpin;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigLiquidCompatibilityBundle extends Bundle
{
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
        $this->sculpin = $container->get('sculpin');
    }

    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $this->sculpin->registerFormatterConfigurationCallback(
            TwigBundle::FORMATTER_NAME,
            array($this, 'configureFormatter')
        );
    }

    public function configureFormatter(Sculpin $sculpin, IFormatter $formatter)
    {
        if ($formatter instanceof TwigFormatter) {
            $formatter->twig()->addTokenParser(new AssignTokenParser());
            $formatter->twig()->addTokenParser(new CaptureTokenParser());
        }
    }

}
