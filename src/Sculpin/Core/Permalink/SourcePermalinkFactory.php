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

use Sculpin\Core\Configuration\Configuration;
use Sculpin\Core\Source\SourceInterface;

/**
 * Source Permalink Factory.
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class SourcePermalinkFactory
{
    /**
     * Configuration
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * Constructor
     *
     * @param Configuration $configuration Configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Create a Permalink for a Source.
     *
     * @param SourceInterface $source Source
     *
     * @return PermalinkInterface
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

        return new Permalink($relativeFilePath, $relativeUrlPath);
    }

    protected function generatePermalinkPathname(SourceInterface $source)
    {
        $pathname = $source->relativePathname();
        $date = $source->data()->get('calculatedDate');
        $title = $source->data()->get('title');
        if (!$permalink = $source->data()->get('permalink')) {
            $permalink = $this->configuration->permalink();
        }
        switch($permalink) {
            case 'none':
                return $pathname;
                break;
            case 'pretty':
                if ($response = $this->isDatePath($pathname)) {
                    return implode('/', array_merge($response, array('index.html')));
                } else {
                    $pretty = preg_replace('/(\.[^\.]+|\.[^\.]+\.[^\.]+)$/', '', $pathname);
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
                list($year, $yr, $month, $mo, $day, $dy) = explode('-', date('Y-y-m-n-d-j', (int) $date));
                $permalink = preg_replace('/:year/', $year, $permalink);
                $permalink = preg_replace('/:yr/', $yr, $permalink);
                $permalink = preg_replace('/:year/', $year, $permalink);
                $permalink = preg_replace('/:month/', $month, $permalink);
                $permalink = preg_replace('/:mo/', $mo, $permalink);
                $permalink = preg_replace('/:day/', $day, $permalink);
                $permalink = preg_replace('/:dy/', $dy, $permalink);
                $permalink = preg_replace('/:title/', $this->normalize($title), $permalink);
                $filename = $pathname;
                if ($isDatePath = $this->isDatePath($pathname)) {
                    $filename = $isDatePath[3];
                }
                $permalink = preg_replace('/:filename/', $filename, $permalink);
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
        if (preg_match('/(\d{4})[\/\-]*(\d{2})[\/\-]*(\d{2})[\/\-]*(.+?)(\.[^\.]+|\.[^\.]+\.[^\.]+)$/', $path, $matches)) {
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
