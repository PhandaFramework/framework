<?php

namespace Phanda\Support\Scene;

use Phanda\Contracts\Support\Scene\SceneFinder;

class SceneName
{
    /**
     * Normalize the given scene name.
     *
     * @param  string  $name
     * @return string
     */
    public static function normalize($name)
    {
        $delimiter = SceneFinder::HINT_PATH_DELIMITER;

        if (strpos($name, $delimiter) === false) {
            return str_replace('/', '.', $name);
        }

        [$namespace, $name] = explode($delimiter, $name);

        return $namespace.$delimiter.str_replace('/', '.', $name);
    }
}