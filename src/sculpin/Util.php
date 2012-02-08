<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin;

class Util {

    /**
     * Test if array is an associative array
     *
     * Note that this function will return true if an array is empty. Meaning
     * empty arrays will be treated as if they are associative arrays.
     *
     * @param array $arr
     * @return boolean
     */
    static public function IS_ASSOC(array $arr)
    {
        return (is_array($arr) && (!count($arr) || count(array_filter(array_keys($arr),'is_string')) == count($arr)));
    }
    
    /**
     * Merge the contents of one thingy into another thingy
     * @param mixed $to
     * @param mixed $from
     * @param bool $clobber
     */
    static public function MERGE_ASSOC_ARRAY($to, $from, $clobber = true)
    {
        if ( is_array($from) ) {
            foreach ( $from as $k => $v ) {
                if ( ! isset($to[$k]) ) {
                    $to[$k] = $v;
                }
                else {
                    $to[$k] = self::MERGE_ASSOC_ARRAY($to[$k], $v, $clobber);
                }
            }
            return $to;
        }
        return $clobber ? $from : $to;
    }

    /**
     * Recursively make directories to to and including specified path
     * @param string $path
     */
    static public function RECURSIVE_MKDIR($path)
    {
        $parent = dirname($path);
        if (!file_exists($parent)) {
            if (!self::RECURSIVE_MKDIR($parent)) {
                return false;
            }
        }
        if (!file_exists($path)) {
            return mkdir($path);
        }
        return true;
    }

    /**
     * Recursively remove files and directories from a path
     * @param string $path
     * @param boolean $onlyRemoveChildren
     */
    static public function RECURSIVE_UNLINK($path, $onlyRemoveChildren = false)
    {
        if (is_link($path) or is_file($path)) {
            unlink($path);
            return;
        }
        if (is_dir($path)) {
            foreach (scandir($path) as $leaf) {
                if ($leaf != "." && $leaf != "..") {
                    self::RECURSIVE_UNLINK($path.'/'.$leaf);
                }
            }
            if (!$onlyRemoveChildren) {
                rmdir($path);
            }
        }
    }
}