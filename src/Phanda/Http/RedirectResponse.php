<?php

namespace Phanda\Http;

use Phanda\Foundation\Http\Request;
use Phanda\Util\Foundation\Http\ResponseTrait;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class RedirectResponse extends SymfonyRedirectResponse
{
    use ResponseTrait;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}