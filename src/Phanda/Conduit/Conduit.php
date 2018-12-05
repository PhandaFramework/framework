<?php

namespace Phanda\Conduit;

use Closure;
use Phanda\Contracts\Conduit\Conduit as ConduitContract;
use Phanda\Contracts\Container\Container;
use Phanda\Contracts\Support\Responsable;
use Phanda\Foundation\Http\Request;
use RuntimeException;

class Conduit implements ConduitContract
{

    /**
     * The container implementation.
     *
     * @var Container
     */
    protected $container;

    /**
     * The object being passed through the pipeline.
     *
     * @var mixed
     */
    protected $fluid;

    /**
     * The array of class pipes.
     *
     * @var array
     */
    protected $channels = [];

    /**
     * The method to call on each pipe.
     *
     * @var string
     */
    protected $method = 'handle';

    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }

    /**
     * Set the traveler object being sent on the conduit.
     *
     * @param  mixed $fluid
     * @return ConduitContract
     */
    public function send($fluid)
    {
        $this->fluid = $fluid;
        return $this;
    }

    /**
     * Set the stops of the conduit.
     *
     * @param  array $channels
     * @return ConduitContract
     */
    public function through($channels)
    {
        $this->channels = is_array($channels) ? $channels : func_get_args();
        return $this;
    }

    /**
     * Set the method to call on the stops.
     *
     * @param  string $method
     * @return ConduitContract
     */
    public function via($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Run the conduit with a final destination callback.
     *
     * @param  \Closure $destination
     * @return mixed
     */
    public function then(Closure $destination)
    {
        $conduit = array_reduce(
            array_reverse($this->channels), $this->carry(), $this->prepareDestination($destination)
        );
        return $conduit($this->fluid);
    }

    /**
     * @param Closure $destination
     * @return Closure
     */
    protected function prepareDestination(Closure $destination)
    {
        return function ($passable) use ($destination) {
            return $destination($passable);
        };
    }

    /**
     * @return Closure
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if (is_callable($pipe)) {
                    return $pipe($passable, $stack);
                } elseif (! is_object($pipe)) {
                    [$name, $parameters] = $this->parseConduitString($pipe);
                    $pipe = $this->getContainer()->create($name);

                    $parameters = array_merge([$passable, $stack], $parameters);
                } else {
                    $parameters = [$passable, $stack];
                }

                $response = method_exists($pipe, $this->method)
                    ? $pipe->{$this->method}(...$parameters)
                    : $pipe(...$parameters);

                return $response instanceof Responsable
                    ? $response->toResponse($this->container->create(Request::class))
                    : $response;
            };
        };
    }

    /**
     * @param $channel
     * @return array
     */
    protected function parseConduitString($channel)
    {
        [$name, $parameters] = array_pad(explode(':', $channel, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        if (! $this->container) {
            throw new RuntimeException('A container instance has not been passed to this Conduit.');
        }

        return $this->container;
    }
}