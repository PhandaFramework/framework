<?php


namespace Phanda\Exceptions\Foundation\Http;

use Phanda\Exceptions\PhandaException;
use Phanda\Foundation\Http\Response;

class HttpResponseException extends PhandaException
{
    /** @var Response */
    protected $response;

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}