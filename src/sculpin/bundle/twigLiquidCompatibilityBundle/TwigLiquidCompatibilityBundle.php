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

use sculpin\bundle\AbstractBundle;
use sculpin\bundle\twigBundle\TwigBundle;
use sculpin\bundle\twigBundle\TwigFormatter;
use sculpin\bundle\twigLiquidCompatibilityBundle\tokenParser\AssignTokenParser;
use sculpin\bundle\twigLiquidCompatibilityBundle\tokenParser\CaptureTokenParser;
use sculpin\formatter\IFormatter;
use sculpin\Sculpin;

/**
 * Twig Liquid Compatibility Bundle
 *
 * Provide some compatibility with with Liquid for Twig. Mainly useful
 * for catching people moving from Jekyll to Sculpin.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class TwigLiquidCompatibilityBundle extends AbstractBundle
{
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

    /**
     * Configuration formatter
     *
     * @param Sculpin    $sculpin   Sculpin
     * @param IFormatter $formatter Formatter
     */
    public function configureFormatter(Sculpin $sculpin, IFormatter $formatter)
    {
        if ($formatter instanceof TwigFormatter) {
            $formatter->twig()->addTokenParser(new AssignTokenParser());
            $formatter->twig()->addTokenParser(new CaptureTokenParser());
        }
    }
}
