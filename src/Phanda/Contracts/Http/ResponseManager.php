<?php

namespace Phanda\Contracts\Http;

use Phanda\Foundation\Http\Response;
use Phanda\Http\JsonResponse;
use Phanda\Http\RedirectResponse;

interface ResponseManager
{
    /**
     * Creates a response.
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public function createResponse($content = '', $status = 200, array $headers = []);

    /**
     * Creates a new "204: no content response"
     *
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public function noContentResponse($status = 204, array $headers = []);

    /**
     * Creates a new response that renders a scene.
     *
     * @param $scene
     * @param array $sceneData
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public function createSceneResponse($scene, array $sceneData = [], $status = 200, array $headers = []);

    /**
     * Creates a new JSON Encoded response.
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return JsonResponse
     */
    public function createJsonResponse($content = '', $status = 200, array $headers = [], $options = 0);

    /**
     * Creates a redirect response to a given url.
     *
     * @param string $url
     * @param int $status
     * @param array $headers
     * @param null $secure
     * @return RedirectResponse
     */
    public function redirectToUrl($url, $status = 302, $headers = [], $secure = null);

    /**
     * Creates a redirect to a given named route.
     *
     * @param string $route
     * @param array $parameters
     * @param int $status
     * @param array $headers
     * @return RedirectResponse
     */
    public function redirectToRoute($route, $parameters = [], $status = 302, $headers = []);
}