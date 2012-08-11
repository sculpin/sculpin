<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\TwigBundle;

use Sculpin\Core\Formatter\FormatContext;
use Sculpin\Core\Formatter\FormatterInterface;

/**
 * Twig Formatter.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class TwigFormatter implements FormatterInterface
{
    /**
     * Twig
     *
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Loader
     *
     * @var \Twig_LoaderInterface
     */
    protected $loader;

    /**
     * Array loader
     *
     * @var \Twig_Loader_Array
     */
    protected $arrayLoader;

    /**
     * Constructor.
     *
     * @param \Twig_LoaderInterface $loader Loader
     */
    public function __construct(\Twig_LoaderInterface $loader)
    {
        $this->arrayLoader = new \Twig_Loader_Array(array());
        $this->loader = new \Twig_Loader_Chain(array($loader, $this->arrayLoader));
        $this->twig = new \Twig_Environment($this->loader);
    }

     /**
     * {@inheritdoc}
     */
    public function formatBlocks(FormatContext $formatContext)
    {
        try {
            $this->arrayLoader->setTemplate($formatContext->templateId(), $this->massageTemplate($formatContext));
            $template = $this->twig->loadTemplate($formatContext->templateId());
            if (!count($blockNames = $template->getBlockNames())) {
                return array('content' => $template->render($formatContext->data()->export()));
            }
            $blocks = array();
            foreach ($blockNames as $blockName) {
                $blocks[$blockName] = $template->renderBlock($blockName, $formatContext->data()->export());
            }

            return $blocks;
        } catch (Exception $e) {
            print " [ exception ]\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function formatPage(FormatContext $formatContext)
    {
        try {
            $this->arrayLoader->setTemplate($formatContext->templateId(), $this->massageTemplate($formatContext));

            return $this->twig->render($formatContext->templateId(), $formatContext->data()->export());
        } catch (Exception $e) {
            print " [ exception ]\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->twig->clearCacheFiles();
        $this->twig->clearTemplateCache();
    }

    protected function massageTemplate(FormatContext $formatContext)
    {
        $template = $formatContext->template();
        if ($layout = $formatContext->data()->get('layout')) {
            if (!preg_match_all('/{%\s+block\s+(\w+)\s+%}(.*?){%\s+endblock\s+%}/si', $template, $matches)) {
                $template = '{% block content %}'.$template.'{% endblock %}';
            }
            $template = '{% extends "' . $layout . '" %}' . $template;
        }

        return $template;
    }
}
