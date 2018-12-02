<?php

namespace Phanda\Foundation\Http\Events;

use Phanda\Events\Event;
use Phanda\Foundation\Http\Request;
use Phanda\Foundation\Http\Response;

class RequestProcessed extends Event
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * RequestProcessed constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}