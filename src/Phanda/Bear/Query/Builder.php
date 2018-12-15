<?php

namespace Phanda\Bear\Query;

use Phanda\Database\Query\Query as DatabaseQueryBuilder;
use Phanda\Contracts\Bear\Query\Builder as QueryBuilderContract;

class Builder extends DatabaseQueryBuilder implements QueryBuilderContract, \JsonSerializable
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