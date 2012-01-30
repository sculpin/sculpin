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

use sculpin\formatter\FormatContext;

use sculpin\formatter\IFormatter;

use sculpin\Sculpin;

class TwigFormatter implements IFormatter
{

    /**
     * Twig
     * @var \Twig_Environment
     */
    protected $twig;
    
    /**
     * Loader
     * @var \Twig_LoaderInterface
     */
    protected $loader;
    
    /**
     * Array loader
     * @var \Twig_Loader_Array
     */
    protected $arrayLoader;
    
    public function __construct(array $viewsPaths, array $extensions)
    {
        
        $this->loader = new \Twig_Loader_Chain(array(
            new FlexibleExtensionFilesystemTwigLoader($viewsPaths, $extensions),
            $this->arrayLoader = new \Twig_Loader_Array(array()),
        ));

        $this->twig = new \Twig_Environment($this->loader);
        
    }
    
    /**
     * (non-PHPdoc)
     * @see sculpin\formatter.IFormatter::formatBlocks()
     */
    public function formatBlocks(Sculpin $sculpin, FormatContext $formatContext)
    {
        try {
            $this->arrayLoader->setTemplate($formatContext->templateId(), $this->massageTemplate($sculpin, $formatContext));
            $template = $this->twig->loadTemplate($formatContext->templateId());
            if (!count($blockNames = $template->getBlockNames())) {
                return array('content' => $template->render($formatContext->context()->export()));
            }
            $blocks = array();
            foreach ($blockNames as $blockName) {
                $blocks[$blockName] = $template->renderBlock($blockName, $formatContext->context()->export());
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
    public function formatPage(Sculpin $sculpin, FormatContext $formatContext)
    {
        try {
            $this->arrayLoader->setTemplate($formatContext->templateId(), $this->massageTemplate($sculpin, $formatContext));
            return $this->twig->render($formatContext->templateId(), $formatContext->context()->export());
        } catch (Exception $e) {
            print " [ exception ]\n";
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see sculpin\formatter.IFormatter::resetFormatter()
     */
    public function resetFormatter()
    {
        $this->twig->clearCacheFiles();
        $this->twig->clearTemplateCache();
    }
    
    protected function massageTemplate(Sculpin $sculpin, FormatContext $formatContext)
    {
        $template = $formatContext->template();
        if ($layout = $formatContext->context()->get('layout')) {
            if (!preg_match_all('/{%\s+block\s+(\w+)\s+%}(.*?){%\s+endblock\s+%}/si',$template,$matches)) {
                $template = '{% block content %}'.$template.'{% endblock %}';
            }
            $template = '{% extends "' . $layout . '" %}' . $template;
        }
        $template = preg_replace('/{% gist .+? %}/', '', $template);
        return $template;
    }
    
    /**
     * Twig
     * @return \Twig_Environment
     */
    public function twig()
    {
        return $this->twig;
    }

}