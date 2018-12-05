<?php

namespace Phanda\Contracts\Support;

use Phanda\Foundation\Http\Request;
use Phanda\Foundation\Http\Response;

interface Responsable
{
    /**
     * @param Request $request
     * @return Response
     */
    public function toResponse(Request $request);
}