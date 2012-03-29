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
     * 
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
}