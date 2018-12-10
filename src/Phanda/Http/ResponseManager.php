<?php

namespace Phanda\Http;

use Phanda\Contracts\Exceptions\ExceptionHandler;
use Phanda\Contracts\Http\ResponseManager as ResponseManagerContract;
use Phanda\Contracts\Routing\Generators\UrlGenerator;
use Phanda\Exceptions\Scene\UnrecognizedExtensionException;
use Phanda\Foundation\Http\Request;
use Phanda\Foundation\Http\Response;
use Phanda\Scene\Factory as SceneFactory;

class ResponseManager implements ResponseManagerContract
{

    /**
     * @var SceneFactory
     */
    protected $sceneFactory;

    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    public function __construct(SceneFactory $sceneFactory, UrlGenerator $urlGenerator)
    {
        $this->sceneFactory = $sceneFactory;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Creates a response.
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public function createResponse($content = '', $status = 200, array $headers = [])
    {
        return new Response($content, $status, $headers);
    }

    /**
     * Creates a new "204: no content response"
     *
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public function noContentResponse($status = 204, array $headers = [])
    {
        return $this->createResponse('', $status, $headers);
    }

    /**
     * Creates a new response that renders a scene.
     *
     * @param $scene
     * @param array $sceneData
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public function createSceneResponse($scene, array $sceneData = [], $status = 200, array $headers = [])
    {
        try {
            return $this->createResponse(
                $this->sceneFactory->create($scene, $sceneData)->render(),
                $status,
                $headers
            );
        } catch (\Throwable $e) {
            /** @var ExceptionHandler $exceptionHandler */
            $exceptionHandler = app()->create(ExceptionHandler::class);
            $exceptionHandler->save($e);
            return $exceptionHandler->render(phanda()->create(Request::class), $e)->send();
        }
    }

    /**
     * Creates a new JSON Encoded response.
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return JsonResponse
     */
    public function createJsonResponse($content = '', $status = 200, array $headers = [], $options = 0)
    {
        return new JsonResponse($content, $status, $headers, $options);
    }

    /**
     * Creates a redirect response to a given url.
     *
     * @param string $url
     * @param int $status
     * @param array $headers
     * @param null $secure
     * @return RedirectResponse
     */
    public function redirectToUrl($url, $status = 302, $headers = [], $secure = null)
    {
        return $this->createRedirect(
            $this->urlGenerator->generate($url, [], $secure),
            $status,
            $headers
        );
    }

    /**
     * Creates a redirect to a given named route.
     *
     * @param string $route
     * @param array $parameters
     * @param int $status
     * @param array $headers
     * @return RedirectResponse
     */
    public function redirectToRoute($route, $parameters = [], $status = 302, $headers = [])
    {
        return $this->createRedirect(
            $this->urlGenerator->generateFromRoute($route, $parameters),
            $status,
            $headers
        );
    }

    /**
     * @param $path
     * @param $status
     * @param $headers
     * @return RedirectResponse
     */
    protected function createRedirect($path, $status, $headers)
    {
        return modify(new RedirectResponse($path, $status, $headers), function ($redirectResponse) {
            /** @var RedirectResponse $redirectResponse */
            $redirectResponse->setRequest($this->urlGenerator->getRequest());
        });
    }
}