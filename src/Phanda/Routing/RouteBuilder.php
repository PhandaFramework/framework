<?php

namespace Phanda\Routing;

use Phanda\Support\Facades\Scene\Scene;

class RouteBuilder
{

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $methods;

    /**
     * @var array
     */
    protected $action;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Phanda\Contracts\Routing\Router
     */
    protected $router;

    public function __construct(\Phanda\Contracts\Routing\Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return $this
     */
    public function anyMethod()
    {
        $this->methods = Router::VERBS;
        return $this;
    }

    /**
     * @param $method
     * @return $this
     */
    public function addMethod($method)
    {
        $method = strtoupper($method);

        if (!in_array($method, Router::VERBS)) {
            throw new \UnexpectedValueException("Method '{$method}' is not one of [" . implode(', ', Router::VERBS) . "]");
        }

        $this->methods[] = $method;
        return $this;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);

        if (!in_array($method, Router::VERBS)) {
            throw new \UnexpectedValueException("Method '{$method}' is not one of [" . implode(', ', Router::VERBS) . "]");
        }

        $this->methods = [$method];
        return $this;
    }

    /**
     * @param $controller
     * @return $this
     */
    public function setController($controller)
    {
        $this->action['controller'] = $controller;
        return $this;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setControllerMethod($method)
    {
        $this->action['method'] = $method;
        return $this;
    }

    /**
     * @param $method
     * @return RouteBuilder
     */
    public function setAction($method)
    {
        return $this->setControllerMethod($method);
    }

    /**
     * @param \Closure $action
     * @return $this
     */
    public function setCallbackAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return RouteBuilder
     */
    public function allowGet()
    {
        return $this->addMethod('GET');
    }

    /**
     * @return RouteBuilder
     */
    public function allowPost()
    {
        return $this->addMethod('POST');
    }

    /**
     * @return RouteBuilder
     */
    public function allowHead()
    {
        return $this->addMethod('HEAD');
    }

    /**
     * @return RouteBuilder
     */
    public function allowPut()
    {
        return $this->addMethod('PUT');
    }

    /**
     * @return RouteBuilder
     */
    public function allowPatch()
    {
        return $this->addMethod('PATCH');
    }

    /**
     * @return RouteBuilder
     */
    public function allowDelete()
    {
        return $this->addMethod('DELETE');
    }

    /**
     * @return RouteBuilder
     */
    public function allowOptions()
    {
        return $this->addMethod('OPTIONS');
    }

    /**
     * @param string $scene
     * @param array $data
     * @return $this
     */
    public function setScene(string $scene, $data = []) {
        $this->action = function() use ($scene, $data) {
            return Scene::render($scene, $data);
        };

        return $this;
    }

    /**
     * @return $this
     */
    public function build()
    {
        if (is_null($this->methods) || empty($this->methods)) {
            $this->anyMethod();
        }

        $this->router->addRoute(
            $this->methods,
            $this->url,
            $this->action,
            $this->name
        );

        return $this;
    }

    /**
     * @return RouteBuilder
     */
    public function newRoute()
    {
        return app()->create(self::class);
    }
}