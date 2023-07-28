<?php

/**
 * Vapm and a brief idea of what it does.>
 * Copyright (C) 2023  VennDev
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

declare(strict_types = 1);

namespace vennv\vapm;

use Fiber;
use Throwable;
use function count;
use function microtime;
use function is_callable;
use function call_user_func;

interface PromiseInterface
{

    /**
     * @throws Throwable
     *
     * This method is used to create a new promise.
     */
    public static function c(callable $callback, bool $justGetResult = false): Promise;

    /**
     * This method is used to get the id of the promise.
     */
    public function getId(): int;

    /**
     * This method is used to get the fiber of the promise.
     */
    public function getFiber(): Fiber;

    /**
     * This method is used to check if the promise is just to get the result.
     */
    public function isJustGetResult(): bool;

    /**
     * This method is used to get the time out of the promise.
     */
    public function getTimeOut(): float;

    /**
     * This method is used to get the time start of the promise.
     */
    public function getTimeStart(): float;

    /**
     * This method is used to get the time end of the promise.
     */
    public function getTimeEnd(): float;

    /**
     * This method is used to set the time out of the promise.
     */
    public function setTimeEnd(float $timeEnd): void;

    /**
     * This method is used to check if the promise is timed out and can be dropped.
     */
    public function canDrop(): bool;

    /**
     * This method is used to get the status of the promise.
     */
    public function getStatus(): string;

    /**
     * This method is used to check if the promise is pending.
     */
    public function isPending(): bool;

    /**
     * This method is used to check if the promise is resolved.
     */
    public function isResolved(): bool;

    /**
     * This method is used to check if the promise is rejected.
     */
    public function isRejected(): bool;

    /**
     * This method is used to get the result of the promise.
     */
    public function getResult(): mixed;

    /**
     * This method is used to get the return when catch or then of the promise is resolved or rejected.
     */
    public function getReturn(): mixed;

    /**
     * @throws Throwable
     *
     * This method is used to get the callback of the promise.
     */
    public function getCallback(): callable;

    /**
     * This method is used to resolve the promise.
     */
    public function resolve(mixed $value): void;

    /**
     * This method is used to reject the promise.
     */
    public function reject(mixed $value): void;

    /**
     * This method is used to set the callback when the promise is resolved.
     */
    public function then(callable $callback): Promise;

    /**
     * This method is used to set the callback when the promise is rejected.
     */
    public function catch(callable $callback): Promise;

    /**
     * This method is used to set the callback when the promise is resolved or rejected.
     */
    public function finally(callable $callback): Promise;

    /**
     * @throws Throwable
     *
     * This method is used to use the callbacks of the promise.
     */
    public function useCallbacks(): void;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function all(array $promises): Promise;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function allSettled(array $promises): Promise;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function any(array $promises): Promise;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function race(array $promises): Promise;

}

final class Promise implements PromiseInterface
{

    private int $id;

    private float $timeOut = 0.0;

    private float $timeEnd = 0.0;

    private mixed $result = null;

    private mixed $return = null;

    private string $status = StatusPromise::PENDING;

    /** @var array<int|string, callable> $callbacksResolve */
    private array $callbacksResolve = [];

    /** @var callable $callbacksReject */
    private mixed $callbackReject;

    /** @var callable $callbackFinally */
    private mixed $callbackFinally;

    private float $timeStart;

    private Fiber $fiber;

    /** @var callable $callback */
    private mixed $callback;

    private bool $justGetResult;

    /**
     * @throws Throwable
     * @param callable $callback
     * @param bool $justGetResult
     */
    public function __construct(callable $callback, bool $justGetResult = false)
    {
        $this->id = EventLoop::generateId();

        $this->callback = $callback;
        $this->fiber = new Fiber($callback);

        if ($justGetResult)
        {
            $this->result = $this->fiber->start();
        }
        else
        {
            $resolve = function($result): void
            {
                $this->resolve($result);
            };

            $reject = function($result): void
            {
                $this->reject($result);
            };

            $this->fiber->start($resolve, $reject);
        }

        if (!$this->fiber->isTerminated())
        {
            FiberManager::wait();
        }

        $this->justGetResult = $justGetResult;

        $this->timeStart = microtime(true);

        $this->callbackReject = function($result): void {};
        $this->callbackFinally = function(): void {};

        EventLoop::addQueue($this);
    }

    /**
     * @throws Throwable
     */
    public static function c(callable $callback, bool $justGetResult = false): Promise
    {
        return new self($callback, $justGetResult);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFiber(): Fiber
    {
        return $this->fiber;
    }

    public function isJustGetResult(): bool
    {
        return $this->justGetResult;
    }

    public function getTimeOut(): float
    {
        return $this->timeOut;
    }

    public function getTimeStart(): float
    {
        return $this->timeStart;
    }

    public function getTimeEnd(): float
    {
        return $this->timeEnd;
    }

    public function setTimeEnd(float $timeEnd): void
    {
        $this->timeEnd = $timeEnd;
    }

    public function canDrop(): bool
    {
        return microtime(true) - $this->timeEnd > Settings::TIME_DROP;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isPending(): bool
    {
        return $this->status === StatusPromise::PENDING;
    }

    public function isResolved(): bool
    {
        return $this->status === StatusPromise::FULFILLED;
    }

    public function isRejected(): bool
    {
        return $this->status === StatusPromise::REJECTED;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function getReturn(): mixed
    {
        return $this->return;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function resolve(mixed $value): void
    {
        if ($this->isPending())
        {
            $this->status = StatusPromise::FULFILLED;
            $this->result = $value;
        }
    }

    public function reject(mixed $value): void
    {
        if ($this->isPending())
        {
            $this->status = StatusPromise::REJECTED;
            $this->result = $value;
        }
    }

    public function then(callable $callback): Promise
    {
        $this->callbacksResolve[] = $callback;

        return $this;
    }

    public function catch(callable $callback): Promise
    {
        $this->callbackReject = $callback;

        return $this;
    }

    public function finally(callable $callback): Promise
    {
        $this->callbackFinally = $callback;

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function useCallbacks(): void
    {
        $result = $this->result;

        if ($this->isResolved())
        {
            $callbacks = $this->callbacksResolve;

            if (count($callbacks) > 0)
            {
                $fiber = new Fiber($callbacks[0]);
                $fiber->start($result);

                $timeStart = microtime(true);

                $isTimeout = false;

                while (!$fiber->isTerminated())
                {
                    $diff = microtime(true) - $timeStart;

                    if ($diff > Settings::TIME_DROP)
                    {
                        $isTimeout = true;
                        $this->timeOut = $diff;
                        $this->status = StatusPromise::REJECTED;
                        $this->result = "Promise timeout";
                        break;
                    }

                }

                if (!$isTimeout)
                {
                    $this->return = $fiber->getReturn();
                    $this->checkStatus($callbacks, $this->return);
                }
            }
        }
        elseif ($this->isRejected())
        {
            if (is_callable($this->callbackReject) && is_callable($this->callbackFinally))
            {
                call_user_func($this->callbackReject, $result);
                call_user_func($this->callbackFinally);
            }
        }
    }

    /**
     * @throws Throwable
     * @param array<callable> $callbacks
     * @phpstan-param array<callable> $callbacks
     */
    private function checkStatus(array $callbacks, mixed $return) : void
    {
        $lastPromise = null;

        while (count($callbacks) > 0)
        {
            $cancel = false;

            foreach ($callbacks as $case => $callable)
            {
                if ($return === null)
                {
                    $cancel = true;
                    break;
                }

                if ($case !== 0 && $return instanceof Promise)
                {
                    EventLoop::addQueue($return);

                    $queue1 = EventLoop::getQueue($return->getId());
                    $queue2 = MicroTask::getTask($return->getId());

                    if (!is_null($queue1)) 
                    {
                        $queue1->then($callable);

                        if (is_callable($this->callbackReject))
                        {
                            $queue1->catch($this->callbackReject);
                        }

                        $lastPromise = $queue1;
                    }
                    elseif (!is_null($queue2))
                    {
                        $queue2->then($callable);

                        if (is_callable($this->callbackReject))
                        {
                            $queue2->catch($this->callbackReject);
                        }

                        $lastPromise = $queue2;
                    }

                    unset($callbacks[$case]);
                    continue;
                }

                if (count($callbacks) === 1)
                {
                    $cancel = true;
                }
            }

            if ($cancel)
            {
                break;
            }
        }

        if ($lastPromise !== null)
        {
            $lastPromise->finally($this->callbackFinally);
        }
        else
        {
            if (is_callable($this->callbackFinally))
            {
                call_user_func($this->callbackFinally);
            }
        }
    }

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function all(array $promises): Promise
    {
        $promise = new Promise(function($resolve, $reject) use ($promises): void
        {
            $results = [];
            $isSolved = false;

            while ($isSolved === false)
            {
                foreach ($promises as $promise)
                {
                    if (is_callable($promise))
                    {
                        $promise = new Async($promise);
                    }

                    if ($promise instanceof Async || $promise instanceof Promise)
                    {
                        $return = EventLoop::getReturn($promise->getId());

                        if ($return !== null)
                        {
                            if ($return->isRejected())
                            {
                                $reject($return->getResult());
                                $isSolved = true;
                            }

                            if ($return->isResolved())
                            {
                                $results[] = $return->getResult();
                            }
                        }
                    }

                    if (count($results) === count($promises))
                    {
                        $resolve($results);
                        $isSolved = true;
                    }
                }

                if ($isSolved === false)
                {
                    FiberManager::wait();
                }
            }
        });

        EventLoop::addQueue($promise);

        return $promise;
    }

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function allSettled(array $promises): Promise
    {
        $promise = new Promise(function($resolve) use ($promises): void
        {
            $results = [];
            $isSolved = false;

            while ($isSolved === false)
            {
                foreach ($promises as $promise)
                {
                    if (is_callable($promise))
                    {
                        $promise = new Async($promise);
                    }

                    if ($promise instanceof Async || $promise instanceof Promise)
                    {
                        $return = EventLoop::getReturn($promise->getId());

                        if ($return !== null)
                        {
                            $results[] = new PromiseResult($return->getStatus(), $return->getResult());
                        }
                    }

                    if (count($results) === count($promises))
                    {
                        $resolve($results);
                        $isSolved = true;
                    }
                }

                if ($isSolved === false)
                {
                    FiberManager::wait();
                }
            }
        });

        EventLoop::addQueue($promise);

        return $promise;
    }

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function any(array $promises): Promise
    {
        $promise = new Promise(function($resolve, $reject) use ($promises): void
        {
            $results = [];
            $isSolved = false;

            while ($isSolved === false)
            {
                foreach ($promises as $promise)
                {
                    if (is_callable($promise))
                    {
                        $promise = new Async($promise);
                    }

                    if ($promise instanceof Async || $promise instanceof Promise)
                    {
                        $return = EventLoop::getReturn($promise->getId());

                        if ($return !== null)
                        {
                            if ($return->isRejected())
                            {
                                $results[] = $return->getResult();
                            }

                            if ($return->isResolved())
                            {
                                $resolve($return->getResult());
                                $isSolved = true;
                            }
                        }
                    }

                    if (count($results) === count($promises))
                    {
                        $reject($results);
                        $isSolved = true;
                    }
                }

                if ($isSolved === false)
                {
                    FiberManager::wait();
                }
            }
        });

        EventLoop::addQueue($promise);

        return $promise;
    }

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function race(array $promises): Promise
    {
        $promise = new Promise(function($resolve, $reject) use ($promises): void
        {
            $isSolved = false;

            while ($isSolved === false)
            {
                foreach ($promises as $promise)
                {
                    if (is_callable($promise))
                    {
                        $promise = new Async($promise);
                    }

                    if ($promise instanceof Async || $promise instanceof Promise)
                    {
                        $return = EventLoop::getReturn($promise->getId());

                        if ($return !== null)
                        {
                            if ($return->isRejected())
                            {
                                $reject($return->getResult());
                                $isSolved = true;
                            }

                            if ($return->isResolved())
                            {
                                $resolve($return->getResult());
                                $isSolved = true;
                            }
                        }
                    }
                }

                if ($isSolved === false)
                {
                    FiberManager::wait();
                }
            }
        });

        EventLoop::addQueue($promise);

        return $promise;
    }

}