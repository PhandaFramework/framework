<?php

namespace Phanda\Contracts\Database\Query;

use Phanda\Contracts\Database\Query\Expression\Expression as ExpressionContract;

interface Query extends ExpressionContract
{
    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_LEFT = 'LEFT';
    const JOIN_TYPE_RIGHT = 'RIGHT';
}