<?php

namespace Phanda\Support;

class PhandArr
{

    public static function MakeArray($value) {
        if(is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

}