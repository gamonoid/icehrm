<?php

/*
 * This file is a part of dflydev/dot-access-data.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dflydev\DotAccessData;

class Util
{
    /**
     * Test if array is an associative array
     *
     * Note that this function will return true if an array is empty. Meaning
     * empty arrays will be treated as if they are associative arrays.
     *
     * @param array $arr
     *
     * @return boolean
     */
    public static function isAssoc(array $arr)
    {
        return (is_array($arr) && (!count($arr) || count(array_filter(array_keys($arr),'is_string')) == count($arr)));
    }

    /**
     * Merge contents from one associtative array to another
     *
     * @param array $to
     * @param array $from
     * @param bool  $clobber
     */
    public static function mergeAssocArray($to, $from, $clobber = true)
    {
        if ( is_array($from) ) {
            foreach ($from as $k => $v) {
                if (!isset($to[$k])) {
                    $to[$k] = $v;
                } else {
                    $to[$k] = self::mergeAssocArray($to[$k], $v, $clobber);
                }
            }

            return $to;
        }

        return $clobber ? $from : $to;
    }
}
