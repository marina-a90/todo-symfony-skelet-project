<?php
namespace App\Utils\Util;

/**
 * Static class containing various utils methods
 */
final class Utils
{
    /**
     * Make array from comma separated string
     *
     * @param $string
     * @return array
     */
    public static function stringToArray($string)
    {
        return explode(",", str_replace(" ", "", $string));
    }
}