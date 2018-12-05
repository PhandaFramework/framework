<?php

namespace Phanda\Http;

use Phanda\Util\Foundation\Http\ResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

class JsonResponse extends SymfonyJsonResponse
{
    use ResponseTrait;

    /**
     * JsonResponse constructor.
     *
     * @param null $data
     * @param int $status
     * @param array $headers
     * @param int $options
     */
    public function __construct($data = null, $status = 200, $headers = [], $options = 0)
    {
        $this->encodingOptions = $options;
        parent::__construct($data, $status, $headers);
    }
}