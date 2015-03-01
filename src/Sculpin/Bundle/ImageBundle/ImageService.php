<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Bundle\ImageBundle;

use Imanee\Imanee;

/**
 * Service for Image Manipulation.
 */
class ImageService
{
    public $env;
    public $imanee;
    public $source_dir;
    public $output_dir;
    public $prefix = '/_thumbs';
    public $completed = array();

    /**
     * Constructor
     *
     * @param Imanee    $imanee         Performs the required image manipulations
     * @param string    $source_dir     Where to find the images
     * @param string    $output_dir     Where to save the images
     * @param string    $env            Current environment type
     */
    public function __construct(Imanee $imanee, $source_dir, $output_dir, $env)
    {
        $this->imanee     = $imanee;
        $this->source_dir = rtrim($source_dir, '/');
        $this->output_dir = rtrim($output_dir, '/');
        $this->env        = $env;
    }

    /**
     * Prepare the output directory.
     *
     * This makes sure we have somewhere to put the thumbnails once we've generated them.
     */
    protected function prepOutputDir()
    {
        if (!is_dir($this->output_dir . $this->prefix)) {
            mkdir($this->output_dir . $this->prefix);
        }
    }

    /**
     * Generate a thumbnail
     *
     * @param string    $image      Path to image file (relative to source_dir)
     * @param int       $width      Width, in pixels (default: 150)
     * @param int       $height     Height, in pixels (default: 150)
     * @param bool      $crop       When set to true, the thumbnail will be cropped
     *                              from the center to match the given size
     *
     * @return string               Location of the thumbnail, for use in <img> tags
     */
    public function thumbnail($image, $width = 150, $height = 150, $crop = false)
    {
        // no sense duplicating work - only process image if thumbnail doesn't already exist
        if (!isset($this->completed[$image][$width][$height][$crop]['filename'])) {
            $this->prepOutputDir();
            $this->imanee->load($this->source_dir . '/' . $image)->thumbnail($width, $height, $crop);
            $thumb_name = vsprintf(
                '%s-%sx%s%s.%s',
                array(
                    $image,
                    $width,
                    $height,
                    ($crop ? '-cropped' : ''),
                    strtolower($this->imanee->getFormat())
                )
            );

            // write the thumbnail to disk
            file_put_contents(
                $this->output_dir . $this->prefix . '/' . $thumb_name,
                $this->imanee->output()
            );
            $this->completed[$image][$width][$height][$crop]['filename'] = $thumb_name;
        }

        return $this->prefix . '/' . $this->completed[$image][$width][$height][$crop]['filename'];
    }
}