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

namespace Sculpin\Bundle\PaginationBundle;

use Sculpin\Core\DataProvider\DataProviderManager;
use Sculpin\Core\Generator\GeneratorInterface;
use Sculpin\Core\Source\SourceInterface;
use Sculpin\Core\Permalink\SourcePermalinkFactory;

/**
 * Pagination Generator.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
final readonly class PaginationGenerator implements GeneratorInterface
{
    public function __construct(
        private DataProviderManager $dataProviderManager,
        private SourcePermalinkFactory $permalinkFactory,
        private int $maxPerPage
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SourceInterface $source): array
    {
        $data = null;
        $config = $source->data()->get('pagination') ?: [];
        if (!isset($config['provider'])) {
            $config['provider'] = 'data.posts';
        }

        if (preg_match('/^(data|page)\.(.+)$/', (string) $config['provider'], $matches)) {
            switch ($matches[1]) {
                case 'data':
                    $data = $this->dataProviderManager->dataProvider($matches[2])->provideData();
                    if ($data === []) {
                        $data = [''];
                    }

                    break;
                case 'page':
                    $data = $source->data()->get($matches[2]);
                    break;
            }
        }

        if (null === $data) {
            return [];
        }

        $maxPerPage = $config['max_per_page'] ?? $this->maxPerPage;

        $slices = [];
        $slice = [];
        $totalItems = 0;
        foreach ($data as $k => $v) {
            if (count($slice) == $maxPerPage) {
                $slices[] = $slice;
                $slice = [];
            }

            $slice[$k] = $v;
            $totalItems++;
        }

        if ($slice !== []) {
            $slices[] = $slice;
        }

        $sources = [];
        $pageNumber = 0;
        foreach ($slices as $slice) {
            $pageNumber++;
            $permalink = null;
            if ($pageNumber > 1) {
                $permalink = $this->permalinkFactory->create($source)->relativeFilePath();
                $basename = basename($permalink);
                if (preg_match('/^(.+?)\.(.+)$/', $basename, $matches)) {
                    if ('index' === $matches[1]) {
                        $paginatedPage = '';
                        $index = '/index';
                    } else {
                        $paginatedPage = $matches[1].'/';
                        $index = '';
                    }

                    $permalink = dirname($permalink).'/'.$paginatedPage.'page/'.$pageNumber.$index.'.'.$matches[2];
                } else {
                    $permalink = dirname($permalink).'/'.$basename.'/page/'.$pageNumber.'.html';
                }

                if (str_starts_with($permalink, './')) {
                    $permalink = substr($permalink, 2);
                }
            }

            $generatedSource = $source->duplicate(
                $source->sourceId().':page='.$pageNumber
            );

            if (null !== $permalink) {
                $generatedSource->data()->set('permalink', $permalink);
            }

            $generatedSource->data()->set('pagination.items', $slice);
            $generatedSource->data()->set('pagination.page', $pageNumber);
            $generatedSource->data()->set('pagination.total_pages', count($slices));
            $generatedSource->data()->set('pagination.total_items', $totalItems);

            $sources[] = $generatedSource;
        }

        $counter = count($sources);

        for ($i = 0; $i < $counter; $i++) {
            $generatedSource = $sources[$i];
            if (0 === $i) {
                $generatedSource->data()->set('pagination.previous_page');
            } else {
                $generatedSource->data()->set('pagination.previous_page', $sources[$i-1]);
            }

            if ($i + 1 < count($sources)) {
                $generatedSource->data()->set('pagination.next_page', $sources[$i+1]);
            } else {
                $generatedSource->data()->set('pagination.next_page');
            }
        }

        return $sources;
    }
}
