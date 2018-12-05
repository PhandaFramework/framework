<?php

namespace Phanda\Contracts\Conduit;

use Closure;

interface Conduit
{
    /**
     * Set the fluid object being sent on the conduit.
     *
     * @param  mixed  $fluid
     * @return $this
     */
    public function send($fluid);

    /**
     * Set the stops of the conduit.
     *
     * @param  array  $channels
     * @return $this
     */
    public function through($channels);

    /**
     * Set the method to call on the stops.
     *
     * @param  string  $method
     * @return $this
     */
    public function via($method);

    /**
     * Run the conduit with a final destination callback.
     *
     * @param  \Closure  $destination
     * @return mixed
     */
    public function then(Closure $destination);
}