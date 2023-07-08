<?php

/*
 * Copyright (c) 2023 VennV
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types = 1);

namespace vennv;

use Fiber;
use Throwable;

final class Promise implements InterfacePromise
{

    private int $id;

    /**
     * @throws Throwable
     */
    public function __construct(callable $callable)
    {
        $fiber = new Fiber($callable);

        $this->id = EventQueue::addQueue(
            $fiber,
            $callable,
            true,
            false,
            0.0
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

        if (!is_null($queue)) 
        {
            $queue->setReturn($result);
            $queue->setStatus(StatusQueue::FULFILLED);
        }
    }

    public static function reject(int $id, mixed $result) : void
    {
        $queue = EventQueue::getQueue($id);

        if (!is_null($queue)) 
        {
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

        if (!is_null($queue)) 
        {
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