<?php

namespace Phanda\Contracts\Database\Query\Expression;

interface Field
{
    /**
     * Sets the fields name
     *
     * @param $field
     * @return Field
     */
    public function setFieldName($field): Field;

    /**
     * Gets the fields name
     *
     * @return mixed
     */
    public function getFieldName();
}