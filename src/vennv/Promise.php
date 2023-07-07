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
    public function __construct(callable $callback, bool $isPromiseAll = false)
    {
        $fiber = new Fiber($callback);

        $this->id = EventQueue::addQueue(
            $fiber,
            true,
            $isPromiseAll
        );
    }

    public function then(callable $callable) : Queue
    {
        EventQueue::getQueue($this->id)->setCallableResolve($callable);
        return EventQueue::getQueue($this->id)->thenPromise($callable);
    }

    public function catch(callable $callable) : Queue
    {
        EventQueue::getQueue($this->id)->setCallableReject($callable);
        return EventQueue::getQueue($this->id)->catchPromise($callable);
    }

    public static function resolve(int $id, mixed $result) : void
    {
        EventQueue::getQueue($id)->setReturn($result);
        EventQueue::getQueue($id)->setStatus(StatusQueue::FULFILLED);
    }

    public static function reject(int $id, mixed $result) : void
    {
        EventQueue::getQueue($id)->setReturn($result);
        EventQueue::getQueue($id)->setStatus(StatusQueue::REJECTED);
    }

    /**
     * @throws Throwable
     */
    public static function all(array $promises) : Promise
    {
        $promise = new Promise(function($resolve, $reject) {}, true);
        EventQueue::getQueue($promise->getId())->setWaitingPromises($promises);
        return $promise;
    }

    /**
     * @throws Throwable
     */
    public static function race(array $promises) : Promise
    {
        $promise = new Promise(function($resolve, $reject) {}, true);
        EventQueue::getQueue($promise->getId())->setWaitingPromises($promises);
        EventQueue::getQueue($promise->getId())->setRacePromise(true);
        return $promise;
    }

    /**
     * @throws Throwable
     */
    public static function any(array $promises) : Promise
    {
        $promise = new Promise(function($resolve, $reject) {}, true);
        EventQueue::getQueue($promise->getId())->setWaitingPromises($promises);
        EventQueue::getQueue($promise->getId())->setAnyPromise(true);
        return $promise;
    }

    /**
     * @throws Throwable
     */
    public static function allSettled(array $promises) : Promise
    {
        $promise = new Promise(function($resolve, $reject) {}, true);
        EventQueue::getQueue($promise->getId())->setWaitingPromises($promises);
        EventQueue::getQueue($promise->getId())->setAllSettled(true);
        return $promise;
    }

    public function getId() : int
    {
        return $this->id;
    }

}