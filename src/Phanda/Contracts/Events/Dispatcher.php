<?php


namespace Phanda\Contracts\Events;


interface Dispatcher
{
    /**
     * @param string $eventName
     * @param mixed $payload
     * @return array|null
     */
    public function dispatch($eventName, $payload = []);

    /**
     * @param string $eventName
     * @param callable $listener
     */
    public function addListener($eventName, $listener);

    /**
     * @param Subscriber $subscriber
     */
    public function addSubscriber(Subscriber $subscriber);

    /**
     * @param string $eventName
     * @param callable $listener
     */
    public function removeListener($eventName, $listener);

    /**
     * @param string $eventName
     */
    public function removeListeners($eventName);

    /**
     * @param Subscriber $subscriber
     */
    public function removeSubscriber(Subscriber $subscriber);

    /**
     * @param string|null $eventName
     * @return array
     */
    public function listeners($eventName = null);

    /**
     * @param string|null $eventName
     * @return bool
     */
    public function hasListeners($eventName = null);

    /**
     * @param string $eventName
     * @param mixed $payload
     */
    public function queue($eventName, $payload = []);

    /**
     * Removes all queued events
     */
    public function removeQueued();
}