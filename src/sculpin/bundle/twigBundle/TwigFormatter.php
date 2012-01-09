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

use sculpin\formatter\IFormatter;

use sculpin\Sculpin;

class TwigFormatter implements IFormatter
{

    /**
     * Twig
     * @var \Twig_Environment
     */
    protected $twig;
    
    public function __construct($viewsPath)
    {
        
        $this->twig = new \Twig_Environment(new \Twig_Loader_Chain(array(
                new \Twig_Loader_Filesystem(array($viewsPath)),
                new \Twig_Loader_String(),
        )));
        
    }
    
    /**
     * (non-PHPdoc)
     * @see sculpin\formatter.IFormatter::formatBlocks()
     */
    public function formatBlocks(Sculpin $sculpin, $template, $context)
    {
        try {
            $template = $this->twig->loadTemplate($this->massageTemplate($sculpin, $template, $context));
            if (!count($blockNames = $template->getBlockNames())) {
                return array('content' => $template->render($context));
            }
            $blocks = array();
            foreach ($blockNames as $blockName) {
                $blocks[$blockName] = $template->renderBlock($blockName, $context);
            }
            return $blocks;
        } catch (Exception $e) {
            print " [ exception ]\n";
        }
    }

    /**
     * (non-PHPdoc)
     * @see sculpin\formatter.IFormatter::formatPage()
     */
    public function formatPage(Sculpin $sculpin, $template, $context)
    {
        try {
            return $this->twig->render($this->massageTemplate($sculpin, $template, $context), $context);
        } catch (Exception $e) {
            print " [ exception ]\n";
        }
    }
    
    protected function massageTemplate(Sculpin $sculpin, $template, $context)
    {
        if (isset($context['layout'])){
            if (!preg_match_all('/{%\s+block\s+(\w+)\s+%}(.*?){%\s+endblock\s+%}/si',$template,$matches)) {
                $template = '{% block content %}'.$template.'{% endblock %}';
            }
            $template = '{% extends "' . $context['layout'] . '" %}' . $template;
        }
        $template = preg_replace('/{% gist .+? %}/', '', $template);
        return $template;
    }

}