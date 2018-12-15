<?php

namespace Phanda\Bear\Query;

use Phanda\Database\Query\Query as DatabaseQueryBuilder;
use Phanda\Contracts\Database\Query\Query as QueryContract;

class Builder extends DatabaseQueryBuilder implements QueryContract, \JsonSerializable
{

    /**
     * Specify data which should be serialized to JSON
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
}