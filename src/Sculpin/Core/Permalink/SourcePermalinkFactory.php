<?php

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
 * Source Permalink Factory.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SourcePermalinkFactory implements SourcePermalinkFactoryInterface
{
    /**
     * Default permalink
     *
     * @var string
     */
    protected $defaultPermalink;

    /**
     * Constructor
     *
     * @param string $defaultPermalink Default permalink
     */
    public function __construct($defaultPermalink)
    {
        $this->defaultPermalink = $defaultPermalink;
    }

    /**
     * {@inheritdoc}
     */
    public function create(SourceInterface $source)
    {
        if ($source->canBeFormatted()) {
            $relativeFilePath = $this->generatePermalinkPathname($source);
            // TODO: Make this configurable... not all index files are named index.*
            if (strpos(basename($relativeFilePath), 'index.') === false) {
                $relativeUrlPath = $relativeFilePath;
            } else {
                $relativeUrlPath = '/'.dirname($relativeFilePath);
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
        if (!$permalink = $source->data()->get('permalink')) {
            $permalink = $this->defaultPermalink;
        }
        switch ($permalink) {
            case 'none':
                return $pathname;
                break;
            case 'pretty':
                if ($response = $this->isDatePath($pathname)) {
                    return implode('/', array_merge($response, array('index.html')));
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
                break;
            default:
                $permalink = preg_replace('/:year/',  date('Y', (int) $date), $permalink);
                $permalink = preg_replace('/:yr/',    date('y', (int) $date), $permalink);
                $permalink = preg_replace('/:month/', date('m', (int) $date), $permalink);
                $permalink = preg_replace('/:mo/',    date('n', (int) $date), $permalink);
                $permalink = preg_replace('/:day/',   date('d', (int) $date), $permalink);
                $permalink = preg_replace('/:dy/',    date('j', (int) $date), $permalink);
                $permalink = preg_replace('/:title/', $this->normalize($title), $permalink);
                $permalink = preg_replace('/:slug_title/', $this->normalize($slug ?: $title), $permalink);
                unset($year, $yr, $month, $mo, $day, $dy);

                $filename = $pathname;
                if ($isDatePath = $this->isDatePath($pathname)) {
                    $filename = $isDatePath[3];
                }
                $permalink = preg_replace('/:filename/', $filename, $permalink);
                $permalink = preg_replace('/:slug_filename/', $this->normalize($slug ?: $filename), $permalink);
                if (strrpos($filename, DIRECTORY_SEPARATOR) !== false) {
                    $basename = substr($filename, strrpos($filename, DIRECTORY_SEPARATOR)+1);
                } else {
                    $basename = $filename;
                }


                $prettyBasename = $basename;
                if (false !== strrpos($basename, '.')) {
                    $prettyBasename =  substr($basename, 0, strrpos($basename, '.'));
                }

                $permalink = preg_replace('/:basename_real/', $basename, $permalink);
                $permalink = preg_replace('/:basename/', $prettyBasename, $permalink);
                if (substr($permalink, -1, 1) == '/') {
                    $permalink .= 'index.html';
                }

                return $permalink;
                break;
        }
    }

    /**
     * Does the specified path represent a date?
     *
     * @param string $path
     *
     * @return mixed
     */
    private function isDatePath($path)
    {
        if (preg_match(
            '/(\d{4})[\/\-]*(\d{2})[\/\-]*(\d{2})[\/\-]*(.+?)(\.[^\.]+|\.[^\.]+\.[^\.]+)$/',
            $path,
            $matches
        )
        ) {
            return array($matches[1], $matches[2], $matches[3], $matches[4]);
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
    private function normalize($param, $space = '-')
    {
        $param = trim($param);
        if (function_exists('iconv')) {
            $param = @iconv('utf-8', 'us-ascii//TRANSLIT', $param);
        }
        $param = preg_replace('/[^a-zA-Z0-9 -]/', '', $param);
        $param = strtolower($param);
        $param = preg_replace('/[\s-]+/', $space, $param);

        return $param;
    }
}
