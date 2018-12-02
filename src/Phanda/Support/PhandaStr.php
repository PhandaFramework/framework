<?php

namespace Phanda\Support;

class PhandaStr
{

    /**
     * @param string|array $needles
     * @param string $haystack
     * @return bool
     */
    public static function endsIn($needles, $haystack) {
        foreach(PhandArr::makeArray($needles) as $needle) {
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string|array $needles
     * @param string $haystack
     * @return bool
     */
    public static function contains($needles, $haystack)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $value
     * @param string|null $encoding
     * @return int
     */
    public static function length($value, $encoding = null)
    {
        if ($encoding) {
            return mb_strlen($value, $encoding);
        }

        return mb_strlen($value);
    }

}