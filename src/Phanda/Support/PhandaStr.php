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
        foreach(PhandArr::MakeArray($needles) as $needle) {
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }

}