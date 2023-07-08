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

interface InterfaceQueue
{

    /**
     * This method to get the id for the queue.
     */
    public function getId() : int;

    /**
     * This method to get the fiber for the queue.
     */
    public function getFiber() : Fiber;

    /**
     * This method to get the timeout for the queue.
     */
    public function getTimeOut() : float;

    /**
     * This method to get the status for the queue.
     */
    public function getStatus() : StatusQueue;

    /**
     * This method to set the status for the queue.
     */
    public function setStatus(StatusQueue $status) : void;

    /**
     * This method to check if the queue is a promise.
     */
    public function isPromise() : bool;

    /**
     * This method to get the time start for the queue.
     */
    public function getTimeStart() : float;

    /**
     * This method to get result of a queue.
     */
    public function getReturn() : mixed;

    /**
     * This method to set result of a queue.
     */
    public function setReturn(mixed $return) : void;

    /**
     * @throws Throwable
     *
     * This method to run a callback for a queue when the queue is fulfilled.
     */
    public function useCallableResolve(mixed $result) : void;

    /**
     * This method to set a callback resolve for a queue.
     */
    public function setCallableResolve(callable $callableResolve) : Queue;

    /**
     * @throws Throwable
     *
     * This method to run a callback for a queue when the queue is rejected.
     */
    public function useCallableReject(mixed $result) : void;

    /**
     * This method to set a callback reject for a queue.
     */
    public function setCallableReject(callable $callableReject) : Queue;

    /**
     * This method to get result of a queue when the queue is resolved.
     */
    public function getReturnResolve() : mixed;

    /**
     * This method to get result of a queue when the queue is rejected.
     */
    public function getReturnReject() : mixed;

    /**
     * This method to catch result of a queue parent when the queue is resolved.
     */
    public function thenPromise(callable $callable) : Queue;

    /**
     * This method to catch result of a queue parent when the queue is rejected.
     */
    public function catchPromise(callable $callable) : Queue;

    /**
     * This method to catch result of a queue child when the queue is resolved.
     */
    public function then(callable $callable) : Queue;

    /**
     * This method to catch result of a queue child when the queue is rejected.
     */
    public function catch(callable $callable) : Queue;

    /**
     * This method to check should drop queue when the queue is resolved or rejected.
     */
    public function canDrop() : bool;

    /**
     * @return  array<callable|Async|Promise>
     *
     * This method to get waiting promises.
     */
    public function getWaitingPromises() : array;

    /**
     * @param array<callable|Async|Promise> $waitingPromises
     *
     * This method to set waiting promises.
     */
    public function setWaitingPromises(array $waitingPromises) : void;

    /**
     * This method to check if the queue is repeatable.
     */
    public function isRepeatable() : bool;

    /**
     * This method to set the queue is a promise race.
     */
    public function setRacePromise(bool $isRacePromise) : void;

    /**
     * This method to check if the queue is a promise race.
     */
    public function isRacePromise() : bool;

    /**
     * This method to set the queue is a promise any.
     */
    public function setAnyPromise(bool $isAnyPromise) : void;

    /**
     * This method to check if the queue is a promise any.
     */
    public function isAnyPromise() : bool;

    /**
     * This method to set the queue is a promise all settled.
     */
    public function setAllSettled(bool $isAllSettled) : void;

    /**
     * This method to check if the queue is a promise all settled.
     */
    public function isAllSettled() : bool;

    /**
     * This method check if the queue is a promise for all.
     */
    public function isPromiseAll() : bool;

    /**
     * This method to set the queue is a promise all.
     */
    public function setPromiseAll(bool $isPromiseAll) : void;

    /**
     * @return mixed
     * 
     * This method to get the Callable.
     */
    public function getPromiseCallable() : mixed;

    /**
     * @throws Throwable
     *
     * This method to check if the queue has completed all promises.
     */
    public function hasCompletedAllPromises() : bool;

}