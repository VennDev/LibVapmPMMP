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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types = 1);

namespace vennv\vapm;

use Fiber;
use Throwable;

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
     */
    public function __construct(callable $callback, bool $justGetResult = false)
    {
        $this->id = EventLoop::generateId();

        $this->callback = $callback;
        $this->fiber = new Fiber($callback);

        if ($justGetResult)
        {
            $this->fiber->start();
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

            if (is_callable($this->callbackFinally))
            {
                call_user_func($this->callbackFinally);
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
                    }
                    elseif (!is_null($queue2))
                    {
                        $queue2->then($callable);

                        if (is_callable($this->callbackReject))
                        {
                            $queue2->catch($this->callbackReject);
                        }
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
                }

                if (count($results) === count($promises))
                {
                    $resolve($results);
                    $isSolved = true;
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
                            $results[] = $return;
                        }
                    }
                }

                if (count($results) === count($promises))
                {
                    $resolve($results);
                    $isSolved = true;
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
                }

                if (count($results) === count($promises))
                {
                    $reject($results);
                    $isSolved = true;
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