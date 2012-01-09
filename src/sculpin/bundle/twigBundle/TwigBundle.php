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

    const CONFIG_VIEWS = 'twig.views';
    
    /**
     * (non-PHPdoc)
     * @see sculpin\bundle.AbstractBundle::getBundleEvents()
     */
    static function getBundleEvents()
    {
        return array(/*Sculpin::EVENT_FORMAT => 'format'*/);
    }
    
    /**
     * (non-PHPdoc)
     * @see sculpin\bundle.AbstractBundle::configureBundle()
     */
    public function configureBundle(Sculpin $sculpin)
    {
        $sculpin->exclude($sculpin->configuration()->get(self::CONFIG_VIEWS).'/**');
        $sculpin->registerFormatter('twig', new TwigFormatter($sculpin->configuration()->getPath(self::CONFIG_VIEWS)));
    }
    
    public function format(SourceFilesChangedEvent $event)
    {
        foreach ($event->inputFiles()->changedFiles() as $inputFile) {
            /* @var $inputFile \sculpin\source\SourceFile */
            try {
                print "-----------------------\n";
                print $inputFile->id() . "\n";
                print "-----------------------\n";
                $context = $inputFile->context();
                $extends = $wrapStart = $wrapEnd = '';
                if ($layout = $inputFile->data()->get('layout')) {
                    $extends = '{% extends "' . $layout . '" %}';
                    if (!isset($context['blocks']) or !count($context['blocks'])) {
                        $wrapStart = '{% block content %}';
                        $wrapEnd = '{% endblock %}';
                    }
                }
                print $event->sculpin()->formatContent($extends . $wrapStart . $inputFile->content() . $wrapEnd, array('page' => $context, 'post' => $context,));
                print "-----------------------\n";
                print "\n\n\n";
                /*
                $context = $inputFile->context();
                if ($layout = $inputFile->data()->get('layout')) {
                    print $event->sculpin->formatContent(
                        $layout,
                        array_merge(
                            $context,
                            array(
                                'page' => $context,
                                'content' => $event->sculpin->formatContent(
                                    $inputFile->content(),
                                    $context
                                )
                            )
                        )
                    );
                } else {
                    print $event->sculpin->formatContent($inputFile->content(), $context);
                }
                */
            } catch (\Twig_Error $twigError) {
                //print $twigError->getMessage() . "\n";
                //
            }
        }
    }

}
