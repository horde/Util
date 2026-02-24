<?php

/**
 * The Horde_Array:: class provides various methods for array manipulation.
 *
 * Copyright 2003-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Michael Slusarz <slusarz@horde.org>
 * @author   Marko Djukic <marko@oblo.com>
 * @author   Jan Schneider <jan@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Util
 */
class Horde_Array
{
    /**
     * Sorts an array on a specified key. If the key does not exist,
     * defaults to the first key of the array.
     *
     * @param array &$array   The array to be sorted, passed by reference.
     * @param string $key     The key by which to sort. If not specified then
     *                        the first key is used.
     * @param integer $dir    Sort direction:
     *                          0 = ascending (default)
     *                          1 = descending
     * @param boolean $assoc  Keep key value association?
     */
    public static function arraySort(array &$array, $key = null, $dir = 0, $assoc = true)
    {
        /* Return if the array is empty. */
        if (!$array) {
            return;
        }

        /* If no key to sort by is specified, use the first key of the
         * first element. */
        if (is_null($key)) {
            $keys = array_keys(reset($array));
            $key = array_shift($keys);
        }

        /* Call the appropriate sort function. */
        $helper = new Horde_Array_Sort_Helper();
        $helper->key = $key;
        $function = $dir ? 'reverseCompare' : 'compare';
        if ($assoc) {
            uasort($array, [ $helper, $function ]);
        } else {
            usort($array, [ $helper, $function ]);
        }
    }

    /**
     * Given an HTML type array field "example[key1][key2][key3]" breaks up
     * the keys so that they could be used to reference a regular PHP array.
     *
     * @param string $field  The field name to be examined.
     * @param string &$base  Will be set to the base element.
     * @param array &$keys   Will be set to the list of keys.
     *
     * @return boolean  True on success, false on error.
     */
    public static function getArrayParts($field, &$base, &$keys)
    {
        if (!preg_match('|([^\[]*)((\[[^\[\]]*\])+)|', $field, $matches)) {
            return false;
        }

        $base = $matches[1];
        $keys = explode('][', $matches[2]);
        $keys[0] = substr($keys[0], 1);
        $keys[count($keys) - 1] = substr($keys[count($keys) - 1], 0, strlen($keys[count($keys) - 1]) - 1);
        return true;
    }

    /**
     * Given an HTML type array field "example[key1][key2][key3]" breaks up
     * the keys into [ 'example', 'key1', 'key2', 'key3' ] so that they could be
     * used to reference a regular PHP array.
     *
     * @param string $field  The field name to be examined.
     *
     * @return array|null    Array of keys, null on error.
     */
    public static function getFieldParts($field)
    {
        if (preg_match('|^([^\[]*)((\[[^\[\]]*\])*)$|', $field, $matches)) {
            $keys = [ $matches[1] ];
            if (strlen($matches[2])) {
                $keys = array_merge($keys, explode('][', substr($matches[2], 1, -1)));
            }
            return $keys;
        }
        return null;
    }

    /**
     * Using an array of keys iterate through the array following the
     * keys to find the final key value.
     *
     * @param array $array        The array to be used.
     * @param array|string $keys  The key path to follow as an array or string.
     * @param string $extraKey    Additional key to add at position 1, if any.
     *
     * @return mixed              The final value of the key path.
     */
    public static function getElement(array $array, $keys, $extraKey = null)
    {
        $ref = &$array;

        if (!is_array($keys)) {
            $keys = [ $keys ];
        }

        if (isset($extraKey)) {
            array_splice($keys, 1, 0, $extraKey);
        }

        foreach ($keys as $key) {
            if (!isset($ref[$key])) {
                return null;
            }
            $ref = &$ref[$key];
        }

        return $ref;
    }

    /**
     * Using an array of keys iterate through the $array following the
     * nested $keys to find the final key's value. If a $value is passed then set
     * that value. If missing, create array levels along that path.
     * Existing value in that path will be overwritten.
     *
     * @param array &$array       The array to be used.
     * @param array|string $keys  The key path to follow as an array or string.
     * @param mixed $value        Target element will have this value set to it.
     * @param string $extraKey    Additional key to add at position 1, if any.
     */
    public static function setElement(array &$array, $keys, $value, $extraKey = null)
    {
        $ref = &$array;

        if (!is_array($keys)) {
            $keys = [ $keys ];
        }

        if (isset($extraKey)) {
            array_splice($keys, 1, 0, $extraKey);
        }

        foreach ($keys as $key) {
            if (!isset($ref[$key])) {
                $ref[$key] = [];
            }
            $ref = &$ref[$key];
        }

        $ref = $value;
    }

    /**
     * Returns a rectangle of a two-dimensional array.
     *
     * @param array   $array   The array to extract the rectangle from.
     * @param integer $row     The start row of the rectangle.
     * @param integer $col     The start column of the rectangle.
     * @param integer $height  The height of the rectangle.
     * @param integer $width   The width of the rectangle.
     *
     * @return array  The extracted rectangle.
     */
    public static function getRectangle(array $array, $row, $col, $height, $width)
    {
        $rec = [];
        for ($y = $row; $y < $row + $height; $y++) {
            $rec[] = array_slice($array[$y], $col, $width);
        }
        return $rec;
    }

    /**
     * Given an array, returns an associative array with each element key
     * derived from its value.
     * For example:
     *   [ 0 => 'foo', 1 => 'bar' ]
     * would become:
     *   [ 'foo' => 'foo', 'bar' => 'bar' ]
     *
     * @param array $array  An array of values.
     *
     * @return array  An array with keys the same as values.
     */
    public static function valuesToKeys(array $array)
    {
        return $array
            ? array_combine($array, $array)
            : [];
    }
}
