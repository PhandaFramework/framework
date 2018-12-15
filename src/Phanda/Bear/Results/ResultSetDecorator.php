<?php

namespace Phanda\Bear\Results;

use Phanda\Contracts\Bear\Query\ResultSet as ResultSetContract;
use Phanda\Dictionary\Dictionary;

class ResultSetDecorator extends Dictionary implements ResultSetContract
{

    /**
     * Gets the count of the internal items
     *
     * @return int
     */
    public function count()
    {
        return count($this->toArray());
    }

}