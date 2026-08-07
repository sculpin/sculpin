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

namespace Sculpin\Core\Permalink;

use Sculpin\Core\Source\SourceInterface;

/**
 * @author Beau Simensen <beau@dflydev.com>
 */
class SourcePermalinkFactory implements SourcePermalinkFactoryInterface
{
    public function __construct(protected string $defaultPermalink)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function create(SourceInterface $source): PermalinkInterface
    {
        if ($source->canBeFormatted()) {
            $relativeFilePath = $this->generatePermalinkPathname($source);
            // TODO: Make this configurable... not all index files are named index.*
            if (!str_contains(basename($relativeFilePath), 'index.')) {
                $relativeUrlPath = $relativeFilePath;
            } else {
                $relativeUrlPath = dirname($relativeFilePath);

                // Check for trailing slashes
                $permalink = $this->getPermaLinkTemplate($source);
                if (str_ends_with($permalink, '/')) {
                    $relativeUrlPath .= '/';
                }
            }

            if ($relativeUrlPath === '/.') {
                $relativeUrlPath = '/';
            }
        } else {
            $relativeFilePath = $source->relativePathname();
            $relativeUrlPath = $relativeFilePath;
        }

        if (!str_starts_with($relativeUrlPath, '/')) {
            $relativeUrlPath = '/'.$relativeUrlPath;
        }

        // Sanitizing url from windows file path
        $relativeUrlPath = str_replace('\\', '/', $relativeUrlPath);

        return new Permalink($relativeFilePath, $relativeUrlPath);
    }

    protected function generatePermalinkPathname(SourceInterface $source): string
    {
        $pathname = $source->relativePathname();
        // Make sure that twig files end up as .html files.
        $pathname = preg_replace('/(html\.)?twig$|twig\.html$/', 'html', $pathname);

        $date = $source->data()->get('calculated_date');
        $title = $source->data()->get('title');
        $slug = $source->data()->get('slug');
        $permalink = $this->getPermaLinkTemplate($source);

        switch ($permalink) {
            case 'none':
                return $pathname;
            case 'pretty':
                if ($response = $this->isDatePath($pathname)) {
                    return implode('/', array_merge($response, ['index.html']));
                }

                $pretty = preg_replace('/(\.[^\.\/]+|\.[^\.\/]+\.[^\.\/]+)$/', '', (string) $pathname);
                return basename((string) $pretty) === 'index' ? $pretty . '.html' : $pretty . '/index.html';
            case 'date':
                if ($response = $this->isDatePath($pathname)) {
                    return implode('/', $response) . '.html';
                }

                return preg_replace('/(\.[^\.]+|\.[^\.]+\.[^\.]+)$/', '', (string) $pathname) . '.html';
            default:
                [$year, $yr, $month, $mo, $day, $dy] = explode('-', date('Y-y-m-n-d-j', (int) $date));
                $permalink = preg_replace('/:year/', $year, $permalink);
                $permalink = preg_replace('/:yr/', $yr, (string) $permalink);
                $permalink = preg_replace('/:month/', $month, (string) $permalink);
                $permalink = preg_replace('/:mo/', $mo, (string) $permalink);
                $permalink = preg_replace('/:day/', $day, (string) $permalink);
                $permalink = preg_replace('/:dy/', $dy, (string) $permalink);
                $permalink = preg_replace('/:title/', $this->normalize((string)$title), (string) $permalink);
                $permalink = preg_replace(
                    '/:slug_title/',
                    (string)($slug ?: $this->normalize((string)$title)),
                    (string)$permalink
                );
                $filename = $pathname;
                if ($isDatePath = $this->isDatePath($pathname)) {
                    $filename = $isDatePath[3];
                }

                $permalink = preg_replace('/:filename/', (string) $filename, (string) $permalink);
                $permalink = preg_replace(
                    '/:slug_filename/',
                    (string)($slug ?: $this->normalize((string)$filename)),
                    (string)$permalink
                );
                if (strrpos((string)$filename, DIRECTORY_SEPARATOR) !== false) {
                    $basename = substr(
                        (string)$filename,
                        strrpos((string)$filename, DIRECTORY_SEPARATOR) + 1
                    );
                } else {
                    $basename = $filename;
                }

                $prettyBasename = false !== strrpos((string) $basename, '.')
                    ? substr((string) $basename, 0, strrpos((string) $basename, '.'))
                    : $basename;
                $permalink = preg_replace('/:basename_real/', (string) $basename, (string) $permalink);
                $permalink = preg_replace('/:basename/', (string) $prettyBasename, (string) $permalink);

                $folder = '';
                // Find FIRST position here
                $folderPos = strpos((string) $pathname, DIRECTORY_SEPARATOR);
                if ($folderPos !== false) {
                    $folderTemp = $pathname;

                    // Strip the first folder if it's a type folder
                    if (str_starts_with((string) $pathname, '_')) {
                        $folderTemp = substr((string) $pathname, $folderPos+1);
                    }

                    // Now check for actual subfolders we are interested in here
                    // Find LAST position here
                    $lastFolderPos = strrpos((string) $folderTemp, DIRECTORY_SEPARATOR);

                    if ($lastFolderPos !== false) {
                        $folder = substr((string) $folderTemp, 0, $lastFolderPos) . '/';
                    }
                }

                $permalink = preg_replace('/:folder/', $folder, (string) $permalink);

                if (preg_match('#(^|[\\/])[^.]+$#', (string) $permalink)
                    // Exclude .md and .twig for BC
                    || str_ends_with((string) $permalink, '.md')
                    || str_ends_with((string) $permalink, '.twig')
                ) {
                    $permalink = rtrim((string) $permalink, '/') . '/';
                }

                if (str_ends_with((string) $permalink, '/')) {
                    $permalink .= 'index.html';
                }

                return $permalink;
        }
    }

    /**
     * Getting the permalink template
     *
     * @param SourceInterface $source Data source
     *
     * @return string Template for permalink
     */
    private function getPermaLinkTemplate(SourceInterface $source): string
    {
        $permalink = $source->data()->get('permalink');

        if (!$permalink) {
            return $this->defaultPermalink;
        }

        return $permalink;
    }

    /**
     * Does the specified path represent a date?
     *
     *
     */
    private function isDatePath(string $path): ?array
    {
        if (preg_match(
            '/(\d{4})[\/\-]*(\d{2})[\/\-]*(\d{2})[\/\-]*(.+?)(\.[^\.]+|\.[^\.]+\.[^\.]+)$/',
            $path,
            $matches
        )) {
            return [$matches[1], $matches[2], $matches[3], $matches[4]];
        }

        return null;
    }

    /**
     * Normalize parameter to be used in human readable URL
     *
     * "Inspired" by Phrozn's normalize implementation.
     *
     * @param string $param Parameter to normalize
     * @param string $space What to use as space separator
     */
    private function normalize(string $param, string $space = '-'): string
    {
        $param = trim($param);
        if (function_exists('iconv')) {
            // Try to transliterate accented characters
            $converted = @iconv('utf-8', 'us-ascii//TRANSLIT', $param);
            // Only overwrite $param if conversion was successful
            if ($converted !== false) {
                $param = $converted;
            }
        }

        $param = preg_replace('/[^a-zA-Z0-9 -]/', '', $param);
        $param = strtolower((string) $param);

        return preg_replace('/[\s-]+/', $space, $param);
    }
}
