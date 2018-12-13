<?php

use Phanda\Container\Container;
use Phanda\Foundation\Application;

if (!function_exists('app')) {
    /**
     * @param  string $abstract
     * @param  array $parameters
     * @return mixed|Application
     */
    function app($abstract = null, array $parameters = [])
    {
        return phanda($abstract, $parameters);
    }
}

if (!function_exists('phanda')) {
    /**
     * Get the available container instance.
     *
     * @param  string $abstract
     * @param  array $parameters
     * @return mixed|Application
     */
    function phanda($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->create($abstract, $parameters);
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string $path
     * @return string
     */
    function app_path($path = '')
    {
        return phanda()->appPath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('assets_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string $path
     * @return string
     */
    function assets_path($path = '')
    {
        return phanda()->assetsPath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string $path
     * @return string
     */
    function base_path($path = '')
    {
        return phanda()->basePath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('bootstrap_path')) {
    /**
     * Get the path to the bootstrap of the install.
     *
     * @param  string $path
     * @return string
     */
    function bootstrap_path($path = '')
    {
        return phanda()->bootstrapPath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('environment_path')) {
    /**
     * Get the path to the environment of the install.
     *
     * @param  string $path
     * @return string
     */
    function environment_path($path = '')
    {
        if(strlen($path) > 0) {
            if(strpos($path, '.env') == false) {
                $path = trim($path) . '.env';
            }
        }

        return phanda()->environmentPath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public serving path.
     *
     * @param  string $path
     * @return string
     */
    function public_path($path = '')
    {
        return phanda()->publicPath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('router')) {
    /**
     * Returns the router.
     *
     * @return \Phanda\Contracts\Routing\Router
     */
    function router()
    {
        return phanda()->create(\Phanda\Contracts\Routing\Router::class);
    }
}

if (!function_exists('routeUrl')) {
    /**
     * Returns a routes url by it's name.
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function routeUrl($name, $parameters = [], $absolute = true)
    {
        /** @var \Phanda\Contracts\Routing\Generators\UrlGenerator $urlGenerator */
        $urlGenerator = phanda()->create(\Phanda\Contracts\Routing\Generators\UrlGenerator::class);
        return $urlGenerator->generateFromRoute($name, $parameters, $absolute);
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the path to the storage serving path.
     *
     * @param  string $path
     * @return string
     */
    function storage_path($path = '')
    {
        return phanda()->storagePath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('scene')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string $view
     * @param  array $data
     * @param  array $mergeData
     * @return \Phanda\Contracts\Scene\Scene|\Phanda\Contracts\Scene\Factory
     */
    function scene($view = null, $data = [], $mergeData = [])
    {
        /** @var \Phanda\Contracts\Scene\Factory $factory */
        $factory = phanda()->create(\Phanda\Contracts\Scene\Factory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->create($view, $data, $mergeData);
    }
}

if (!function_exists('url')) {
    /**
     * Generate a url for the application.
     *
     * @param  string $path
     * @param  mixed $parameters
     * @param  bool $secure
     * @return \Phanda\Contracts\Routing\Generators\UrlGenerator|string
     */
    function url($path = null, $parameters = [], $secure = null)
    {
        /** @var \Phanda\Contracts\Routing\Generators\UrlGenerator $urlGenerator */
        $urlGenerator = phanda()->create(\Phanda\Contracts\Routing\Generators\UrlGenerator::class);

        if (is_null($path)) {
            return $urlGenerator;
        }

        return $urlGenerator->generate($path, $parameters, $secure);
    }
}

if (!function_exists('responseManager')) {
    /**
     * Returns the ResponseManager instance.
     *
     * @return \Phanda\Contracts\Http\ResponseManager
     */
    function responseManager()
    {
        /** @var \Phanda\Contracts\Http\ResponseManager $responseManager */
        $responseManager = phanda()->create(\Phanda\Contracts\Http\ResponseManager::class);
        return $responseManager;
    }
}

if(!function_exists('createResponse')) {
    /**
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return \Phanda\Foundation\Http\Response
     */
    function createResponse($content = '', $status = 200, $headers = [])
    {
        /** @var \Phanda\Contracts\Http\ResponseManager $responseManager */
        $responseManager = phanda()->create(\Phanda\Contracts\Http\ResponseManager::class);
        return $responseManager->createResponse($content, $status, $headers);
    }
}