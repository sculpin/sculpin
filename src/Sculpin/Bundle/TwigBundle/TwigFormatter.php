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

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Sculpin\Core\Formatter\FormatContext;
use Sculpin\Core\Formatter\FormatterInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TemplateWrapper;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
final readonly class TwigFormatter implements FormatterInterface
{
    public function __construct(private Environment $twig, private ArrayLoader $arrayLoader)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function formatBlocks(FormatContext $formatContext): array
    {
        $this->arrayLoader->setTemplate(
            $formatContext->templateId(),
            $this->massageTemplate($formatContext)
        );
        $data = $formatContext->data()->export();
        $template = $this->twig->load($formatContext->templateId());

        if (($blockNames = $this->findAllBlocks($template, $data)) === []) {
            return ['content' => $template->render($data)];
        }

        $blocks = [];
        foreach ($blockNames as $blockName) {
            $blocks[$blockName] = $template->renderBlock($blockName, $data);
        }

        return $blocks;
    }

    public function findAllBlocks(TemplateWrapper $template, array $context): array
    {
        return $template->getBlockNames($context);
    }

    /**
     * {@inheritdoc}
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
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
    public function reset(): void
    {
        // nothing to do
    }

    private function massageTemplate(FormatContext $formatContext): string
    {
        $template = $formatContext->template();
        if ($layout = $formatContext->data()->get('layout')) {
            // Completely remove anything in verbatim sections so that any blocks defined in there will
            // not trigger the "you've already defined blocks!" check since this is almost certainly
            // NOT the intention of the source's author.
            $verbatim = preg_replace('/{%\s+verbatim\s+%}(.*?){%\s+endverbatim\s+%}/si', '', $template);

            if (!preg_match_all('/{%\s+block\s+(\w+)\s+%}(.*?){%\s+endblock\s+%}/si', (string) $verbatim, $matches)) {
                $template = '{% block content %}'.$template.'{% endblock %}';
            }

            $template = '{% extends "' . $layout . '" %}' . $template;
        }

        return $template;
    }
}
