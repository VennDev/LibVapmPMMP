<?php

namespace vennv;

use Fiber;
use Throwable;

final class Promise implements InterfacePromise
{

    private int $id;

    /**
     * @throws Throwable
     */
    public function __construct(callable $callback)
    {
        $fiber = new Fiber($callback);

        $this->id = EventQueue::addQueue(
            $fiber,
            true
        );
    }

    public function then(callable $callable) : ?Queue
    {
        $queue = EventQueue::getQueue($this->id);

        if (!is_null($queue))
        {
            $queue->setCallableResolve($callable);
            return $queue->thenPromise($callable);
        }

        return null;
    }

    public function catch(callable $callable) : ?Queue
    {
        $queue = EventQueue::getQueue($this->id);

        if (!is_null($queue))
        {
            $queue->setCallableReject($callable);
            return $queue->catchPromise($callable);
        }

        return null;
    }

    public static function resolve(int $id, mixed $result) : void
    {
        $queue = EventQueue::getQueue($id);

        if (!is_null($queue)) {
            $queue->setReturn($result);
            $queue->setStatus(StatusQueue::FULFILLED);
        }
    }

    public static function reject(int $id, mixed $result) : void
    {
        $queue = EventQueue::getQueue($id);

        if (!is_null($queue)) {
            $queue->setReturn($result);
            $queue->setStatus(StatusQueue::REJECTED);
        }
    }

    /**
     * @throws Throwable
     * @param array<callable|Async|Promise> $promises
     */
    public static function all(array $promises) : Promise
    {
        $promise = new Promise(function($resolve, $reject) {});
        $queue = EventQueue::getQueue($promise->getId());

        if (!is_null($queue))
        {
            $queue->setWaitingPromises($promises);
            $queue->setPromiseAll(true);
        }

        return $promise;
    }

    /**
     * @throws Throwable
     * @param array<callable|Async|Promise> $promises
     */
    public static function race(array $promises) : Promise
    {
        $promise = new Promise(function($resolve, $reject) {});
        $queue = EventQueue::getQueue($promise->getId());

        if (!is_null($queue)) {
            $queue->setWaitingPromises($promises);
            $queue->setRacePromise(true);
        }

        return $promise;
    }

    /**
     * @throws Throwable
     * @param array<callable|Async|Promise> $promises
     */
    public static function any(array $promises) : Promise
    {
        $promise = new Promise(function($resolve, $reject) {});
        $queue = EventQueue::getQueue($promise->getId());

        if (!is_null($queue))
        {
            $queue->setWaitingPromises($promises);
            $queue->setAnyPromise(true);
        }

        return $promise;
    }

    /**
     * @throws Throwable
     * @param array<callable|Async|Promise> $promises
     */
    public static function allSettled(array $promises) : Promise
    {
        $promise = new Promise(function($resolve, $reject) {});
        $queue = EventQueue::getQueue($promise->getId());

        if (!is_null($queue))
        {
            $queue->setWaitingPromises($promises);
            $queue->setAllSettled(true);
        }

        return $promise;
    }

    public function getId() : int
    {
        return $this->id;
    }

}