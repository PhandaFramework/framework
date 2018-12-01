<?php

namespace Phanda\Events;

use Phanda\Contracts\Events\Dispatcher as DispatcherContract;
use Phanda\Contracts\Events\Subscriber;
use Phanda\Support\PhandArr;

class Dispatcher implements DispatcherContract
{

    protected $listeners = [];

    /**
     * @param string $eventName
     * @param mixed $payload
     * @return array|null
     */
    public function dispatch($eventName, $payload = [])
    {
        [$event, $payload] = $this->parseEventAndPayload($eventName, $payload);

        $listeners = $this->listeners($eventName);

        $responses = [];

        foreach($listeners as $listener) {
            $response = $listener($event, $payload);

            if($response === false) {
                break;
            }

            $responses[] = $response;
        }

        return $responses;
    }

    protected function parseEventAndPayload($event, $payload)
    {
        if(is_object($event)) {
            [$payload, $event] = [[$event], get_class($event)];
        }

        return [$event, PhandArr::MakeArray($payload)];
    }

    /**
     * @param string $eventName
     * @param callable $listener
     */
    public function addListener($eventName, $listener)
    {
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * @param Subscriber $subscriber
     */
    public function addSubscriber(Subscriber $subscriber)
    {
        foreach($subscriber->getSubscribedEvents() as $eventName => $params) {
            if(is_string($params)) {
                $this->addListener($eventName, [$subscriber, $params]);
            } else {
                foreach($params as $listener) {
                    $this->addListener($eventName, [$subscriber, $listener[0]]);
                }
            }
        }
    }

    /**
     * @param string $eventName
     * @param callable $listener
     */
    public function removeListener($eventName, $listener)
    {
        // TODO: Implement removeListener() method.
    }

    /**
     * @param string $eventName
     */
    public function removeListeners($eventName)
    {
        // TODO: Implement removeListeners() method.
    }

    /**
     * @param Subscriber $subscriber
     */
    public function removeSubscriber(Subscriber $subscriber)
    {
        // TODO: Implement removeSubscriber() method.
    }

    /**
     * @param string|null $eventName
     * @return array
     */
    public function listeners($eventName = null)
    {
        // TODO: Implement listeners() method.
    }

    /**
     * @param string|null $eventName
     * @return bool
     */
    public function hasListeners($eventName = null)
    {
        // TODO: Implement hasListeners() method.
    }

    /**
     * @param string $eventName
     * @param mixed $payload
     */
    public function queue($eventName, $payload = [])
    {
        // TODO: Implement queue() method.
    }

    /**
     * Removes all queued events
     */
    public function removeQueued()
    {
        // TODO: Implement removeQueued() method.
    }
}