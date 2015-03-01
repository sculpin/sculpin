<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\ImageBundle;

use Twig_Extension;

/**
 * Exposes a "thumbnail" function to Twig templates
 */
class TwigImageExtension extends Twig_Extension
{
    public $service = null;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function getName()
    {
        return 'image_extension';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('thumbnail', array($this->service, 'thumbnail'))
        );
    }
}