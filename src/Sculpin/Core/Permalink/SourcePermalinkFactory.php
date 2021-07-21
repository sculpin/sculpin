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
    /**
     * Default permalink template for when the source does not specify a template.
     *
     * @var string
     */
    protected $defaultPermalink;

    public function __construct(string $defaultPermalink)
    {
        $this->defaultPermalink = $defaultPermalink;
    }

    /**
     * {@inheritdoc}
     */
    public function create(SourceInterface $source): PermalinkInterface
    {
        if ($source->canBeFormatted()) {
            $relativeFilePath = $this->generatePermalinkPathname($source);
            // TODO: Make this configurable... not all index files are named index.*
            if (strpos(basename($relativeFilePath), 'index.') === false) {
                $relativeUrlPath = $relativeFilePath;
            } else {
                $relativeUrlPath = dirname($relativeFilePath);

                // Check for trailing slashes
                $permalink = $this->getPermaLinkTemplate($source);
                if (substr($permalink, -1, 1) == '/') {
                    $relativeUrlPath .= '/';
                }
            }
            if ($relativeUrlPath == '/.') {
                $relativeUrlPath = '/';
            }
        } else {
            $relativeFilePath = $relativeUrlPath = $source->relativePathname();
        }

        if (0 !== strpos($relativeUrlPath, '/')) {
            $relativeUrlPath = '/'.$relativeUrlPath;
        }

        // Sanitizing url from windows file path
        $relativeUrlPath = str_replace('\\', '/', $relativeUrlPath);

        return new Permalink($relativeFilePath, $relativeUrlPath);
    }

    protected function generatePermalinkPathname(SourceInterface $source)
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
                } else {
                    $pretty = preg_replace('/(\.[^\.\/]+|\.[^\.\/]+\.[^\.\/]+)$/', '', $pathname);
                    if (basename($pretty) == 'index') {
                        return $pretty . '.html';
                    } else {
                        return $pretty . '/index.html';
                    }
                }
                break;
            case 'date':
                if ($response = $this->isDatePath($pathname)) {
                    return implode('/', $response).'.html';
                }

                return preg_replace('/(\.[^\.]+|\.[^\.]+\.[^\.]+)$/', '', $pathname).'.html';
            default:
                [$year, $yr, $month, $mo, $day, $dy] = explode('-', date('Y-y-m-n-d-j', (int) $date));
                $permalink = preg_replace('/:year/', $year, $permalink);
                $permalink = preg_replace('/:yr/', $yr, $permalink);
                $permalink = preg_replace('/:month/', $month, $permalink);
                $permalink = preg_replace('/:mo/', $mo, $permalink);
                $permalink = preg_replace('/:day/', $day, $permalink);
                $permalink = preg_replace('/:dy/', $dy, $permalink);
                $permalink = preg_replace('/:title/', $this->normalize((string)$title), $permalink);
                $permalink = preg_replace('/:slug_title/', $slug ?: $this->normalize((string)$title), $permalink);
                $filename = $pathname;
                if ($isDatePath = $this->isDatePath($pathname)) {
                    $filename = $isDatePath[3];
                }
                $permalink = preg_replace('/:filename/', $filename, $permalink);
                $permalink = preg_replace('/:slug_filename/', $slug ?: $this->normalize((string)$filename), $permalink);
                if (strrpos($filename, DIRECTORY_SEPARATOR) !== false) {
                    $basename = substr($filename, strrpos($filename, DIRECTORY_SEPARATOR)+1);
                } else {
                    $basename = $filename;
                }
                $prettyBasename = false !== strrpos($basename, '.')
                    ? substr($basename, 0, strrpos($basename, '.'))
                    : $basename;
                $permalink = preg_replace('/:basename_real/', $basename, $permalink);
                $permalink = preg_replace('/:basename/', $prettyBasename, $permalink);

                $folder = '';
                // Find FIRST position here
                $folderPos = strpos($pathname, DIRECTORY_SEPARATOR);
                if ($folderPos !== false) {
                    $folderTemp = $pathname;

                    // Strip the first folder if it's a type folder
                    if ('_' === substr($pathname, 0, 1)) {
                        $folderTemp = substr($pathname, $folderPos+1);
                    }

                    // Now check for actual subfolders we are interested in here
                    // Find LAST position here
                    $lastFolderPos = strrpos($folderTemp, DIRECTORY_SEPARATOR);

                    if ($lastFolderPos !== false) {
                        $folder = substr($folderTemp, 0, $lastFolderPos) . '/';
                    }
                }
                $permalink = preg_replace('/:folder/', $folder, $permalink);

                if (preg_match('#(^|[\\/])[^.]+$#', $permalink)
                    // Exclude .md and .twig for BC
                    || substr($permalink, -3, 3) === '.md'
                    || substr($permalink, -5, 5) === '.twig'
                ) {
                    $permalink = rtrim($permalink, '/') . '/';
                }

                if (substr($permalink, -1, 1) == '/') {
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
            $permalink = $this->defaultPermalink;
        }

        return $permalink;
    }

    /**
     * Does the specified path represent a date?
     *
     * @param string $path
     *
     * @return mixed
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
     *
     * @return string
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
        $param = strtolower($param);
        $param = preg_replace('/[\s-]+/', $space, $param);

        return $param;
    }
}
