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
     * @param Event|null $event
     * @return Event
     */
    public function dispatch($eventName, Event $event = null)
    {
        if($event === null) {
            $event = new Event();
        }

        $listeners = $this->listeners($eventName);

        if($listeners) {
            $this->executeDispatch($listeners, $eventName, $event);
        }

        return $event;
    }

    /**
     * @param callable[] $listeners
     * @param string $eventName
     * @param Event $event
     */
    protected function executeDispatch($listeners, $eventName, Event $event)
    {
        foreach($listeners as $listener)
        {
            if($event->isPropagationStopped()) {
                break;
            }

            $listener($event, $eventName, $this);
        }
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
        if(empty($this->listeners[$eventName])) {
            return;
        }

        if(is_array($listener) && isset($listener[0]) && $listener[0] instanceof \Closure) {
            $listener[0] = $listener[0]();
        }
    }

    /**
     * @param string $eventName
     */
    public function removeListeners($eventName)
    {
        unset($this->listeners[$eventName]);
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
        if($eventName !== null) {
            $listeners = $this->listeners[$eventName] ?? [];
            return $listeners;
        }

        return $this->listeners;
    }

    /**
     * @param string|null $eventName
     * @return bool
     */
    public function hasListeners($eventName = null)
    {
        if($eventName !== null) {
            return !empty($this->listeners[$eventName]);
        }

        foreach($this->listeners as $eventListener) {
            if($eventListener) {
                return true;
            }
        }

        return false;
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