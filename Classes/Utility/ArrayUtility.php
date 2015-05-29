<?php
namespace Bluechip\EdPixlr\Utility;

class ArrayUtility
{

    /**
     * Inserts a new key/value before the key in the array.
     *
     * @param string $key The key to insert before.
     * @param array $array An array to insert in to.
     * @param string $newKey The key to insert.
     * @param mixed $content An value to insert.
     * @return void
     */
    public static function insertBefore($key, array &$array, $newKey, $content)
    {
        if (array_key_exists($key, $array)) {
            $new = array();
            foreach ($array as $k => $value) {
                if ($k === $key) {
                    $new[$newKey] = $content;
                }
                $new[$k] = $value;
            }
            $array = $new;
        } else {
            $array[$newKey] = $content;
        }
    }

    /**
     * Inserts a new key/value after the key in the array.
     *
     * @param string $key the key to insert after.
     * @param array $array An array to insert in to.
     * @param string $newKey The key to insert.
     * @param mixed $content An value to insert.
     * @return array The new array if the key exists, FALSE otherwise.
     */
    public static function insertAfter($key, array &$array, $newKey, $content)
    {
        if (array_key_exists($key, $array)) {
            $new = array();
            foreach ($array as $k => $value) {
                $new[$k] = $value;
                if ($k === $key) {
                    $new[$newKey] = $content;
                }
            }
            $array = $new;
        } else {
            $array[$newKey] = $content;
        }
    }
}