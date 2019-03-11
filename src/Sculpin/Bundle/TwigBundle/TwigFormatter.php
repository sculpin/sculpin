<?php

declare(strict_types=1);

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
     * Array loader
     *
     * @var \Twig_Loader_Array
     */
    protected $arrayLoader;

    /**
     * Constructor.
     *
     * @param \Twig_Environment  $twig        Twig
     * @param \Twig_Loader_Array $arrayLoader Array Loader
     */
    public function __construct(\Twig_Environment $twig, \Twig_Loader_Array $arrayLoader)
    {
        $this->twig = $twig;
        $this->arrayLoader = $arrayLoader;
    }

     /**
     * {@inheritdoc}
     */
    public function formatBlocks(FormatContext $formatContext): array
    {
        $this->arrayLoader->setTemplate(
            $formatContext->templateId(),
            $this->massageTemplate($formatContext)
        );
        $data = $formatContext->data()->export();
        $template = $this->twig->resolveTemplate($formatContext->templateId());

        if (!count($blockNames = $this->findAllBlocks($template, $data))) {
            return ['content' => $template->render($data)];
        }
        $blocks = [];
        foreach ($blockNames as $blockName) {
            $blocks[$blockName] = $template->renderBlock($blockName, $data);
        }

        return $blocks;
    }

    public function findAllBlocks(\Twig_Template $template, array $context): array
    {
        return $template->getBlockNames($context);
    }

    /**
     * {@inheritdoc}
     */
    public function formatPage(FormatContext $formatContext): string
    {
        $this->arrayLoader->setTemplate(
            $formatContext->templateId(),
            $this->massageTemplate($formatContext)
        );

        $data = $formatContext->data()->export();

        return $this->twig->render($formatContext->templateId(), $data);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        // cache clearing is completely removed in twig v2
        // $this->twig->clearCacheFiles();
        // $this->twig->clearTemplateCache();
    }

    protected function massageTemplate(FormatContext $formatContext)
    {
        $template = $formatContext->template();
        if ($layout = $formatContext->data()->get('layout')) {
            // Completely remove anything in verbatim sections so that any blocks defined in there will
            // not trigger the "you've already defined blocks!" check since this is almost certainly
            // NOT the intention of the source's author.
            $verbatim = preg_replace('/{%\s+verbatim\s+%}(.*?){%\s+endverbatim\s+%}/si', '', $template);

            if (!preg_match_all('/{%\s+block\s+(\w+)\s+%}(.*?){%\s+endblock\s+%}/si', $verbatim, $matches)) {
                $template = '{% block content %}'.$template.'{% endblock %}';
            }
            $template = '{% extends "' . $layout . '" %}' . $template;
        }

        return $template;
    }
}
