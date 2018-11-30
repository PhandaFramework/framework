<?php


namespace Phanda\Contracts\Support;


interface Jsonable
{
    /**
     * @param int $options
     * @return string
     */
    public function toJson($options = 0);
}