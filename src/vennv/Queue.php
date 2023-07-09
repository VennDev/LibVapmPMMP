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

namespace vennv;

use Exception;
use Fiber;
use Throwable;

final class Queue implements InterfaceQueue
{

    private const MAIN_QUEUE = "Main";

    private float $timeStart;

    private float $timeDrop;

    private mixed $return;

    /**
     * @var array<callable>
     */
    private array $callableResolve;

    /**
     * @var array<callable>
     */
    private array $callableReject;

    private mixed $returnResolve;

    private mixed $returnReject;

    /**
     * @var array<callable|Async|Promise>
     */
    private array $waitingPromises = [];

    private bool $isRacePromise = false;

    private bool $isAnyPromise = false;

    private bool $isAllSettled = false;

    private bool $isPromiseAll = false;

    
    public function __construct(
        private readonly int $id,
        private readonly Fiber $fiber,
        private readonly mixed $promiseCallable,
        private readonly float $timeOut,
        private StatusQueue $status,
        private readonly bool $isPromise,
        private readonly bool $isRepeatable
    )
    {
        $this->timeStart = microtime(true);
        $this->timeDrop = 15;
        $this->return = null;
        $this->callableResolve[self::MAIN_QUEUE] = function($result) {};
        $this->callableReject[self::MAIN_QUEUE] = function($result) {};
        $this->returnResolve = null;
        $this->returnReject = null;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getFiber() : Fiber
    {
        return $this->fiber;
    }

    public function getTimeOut() : float
    {
        return $this->timeOut;
    }

    public function getStatus() : StatusQueue
    {
        return $this->status;
    }

    public function setStatus(StatusQueue $status) : void
    {
        $this->status = $status;
    }

    public function isPromise() : bool
    {
        return $this->isPromise;
    }

    public function getTimeStart() : float
    {
        return $this->timeStart;
    }

    public function getReturn() : mixed
    {
        return $this->return;
    }

    public function setReturn(mixed $return) : void
    {
        $this->return = $return;
    }

    private function getResult(Fiber $fiber) : mixed
    {
        $timeStart = microtime(true);

        while (!$fiber->isTerminated())
        {
            if (microtime(true) - $timeStart > $this->timeDrop)
            {
                break;
            }
        }

        try 
        {
            $result = $fiber->getReturn();
        }
        catch (Exception | Throwable $error)
        {
            $result = $error->getMessage();
            $this->callableResolve = [];
            $this->callableReject = [];
        }

        return $result;
    }

    /**
     * @throws Throwable
     * @param array<callable> $callableFc
     */
    private function checkStatus(array $callableFc, mixed $return) : void
    {
        while (count($callableFc) > 0)
        {
            $firstCheck = false;
            $cancel = false;

            foreach ($callableFc as $id => $callable)
            {
                if ($return === null)
                {
                    $cancel = true;
                    break;
                }
                if (
                    $id !== self::MAIN_QUEUE && 
                    $return instanceof Promise &&
                    !$firstCheck
                )
                {
                    $queue = EventQueue::getQueue($return->getId());

                    if (!is_null($queue))
                    {
                        $queue->setCallableResolve($callable);
                    }
                    $firstCheck = true;
                }
                elseif (
                    $id !== self::MAIN_QUEUE && 
                    $return instanceof Promise
                )
                {
                    $queue = EventQueue::getQueue($return->getId());

                    if (!is_null($queue)) {
                        $queue->then($callable);
                    }

                    unset($callableFc[$id]);
                    continue;
                }
                if (count($callableFc) === 1)
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
     */
    public function useCallableResolve(mixed $result) : void
    {
        if ($this->getStatus() === StatusQueue::FULFILLED)
        {

            $fiber = new Fiber(function() use ($result) {
                return ($this->callableResolve[self::MAIN_QUEUE])($result);
            });
            
            // try 
            // {
            //     $fiber->start();
            // } 
            // catch (Throwable | Exception $error) 
            // {
            //     throw new QueueError($error->getMessage());
            // }
            $fiber->start();

            unset($this->callableResolve[self::MAIN_QUEUE]);

            $this->returnResolve = $this->getResult($fiber);

            $this->checkStatus($this->callableResolve, $this->returnResolve);
        }
    }

    public function setCallableResolve(callable $callableResolve) : Queue
    {
        $this->callableResolve[self::MAIN_QUEUE] = $callableResolve;
        return $this;
    }

    /**
     * @throws Throwable
     */
    public function useCallableReject(mixed $result) : void
    {
        if ($this->getStatus() === StatusQueue::REJECTED)
        {
            $fiber = new Fiber(function () use ($result) {
                return ($this->callableReject[self::MAIN_QUEUE])($result);
            });
            
            // try 
            // {
            //     $fiber->start();
            // } 
            // catch (Throwable | Exception $error) 
            // {
            //     throw new QueueError($error->getMessage());
            // }
            $fiber->start();

            $this->returnReject = $this->getResult($fiber);

            $this->checkStatus($this->callableReject, $this->returnReject);
        }
    }

    public function setCallableReject(callable $callableReject) : Queue
    {
        $this->callableReject[self::MAIN_QUEUE] = $callableReject;
        return $this;
    }

    public function getReturnResolve() : mixed
    {
        return $this->returnResolve;
    }

    public function getReturnReject() : mixed
    {
        return $this->returnReject;
    }

    public function thenPromise(callable $callable) : Queue
    {
        $this->setCallableResolve($callable);
        return $this;
    }

    public function catchPromise(callable $callable) : Queue
    {
        $this->setCallableReject($callable);
        return $this;
    }

    public function then(callable $callable) : Queue
    {
        $this->callableResolve[] = $callable;
        return $this;
    }

    public function catch(callable $callable) : Queue
    {
        $this->setCallableReject($callable);
        return $this;
    }

    public function canDrop() : bool
    {
        return (microtime(true) - $this->timeStart) > $this->timeDrop;
    }

    /** @return  array<callable|Async|Promise> */
    public function getWaitingPromises() : array
    {
        return $this->waitingPromises;
    }

    /**
     * @param array<callable|Async|Promise> $waitingPromises
     */
    public function setWaitingPromises(array $waitingPromises) : void
    {
        $this->waitingPromises = $waitingPromises;
    }

    public function isRepeatable() : bool
    {
        return $this->isRepeatable;
    }

    public function setRacePromise(bool $isRacePromise) : void
    {
        $this->isRacePromise = $isRacePromise;
    }

    public function isRacePromise() : bool
    {
        return $this->isRacePromise;
    }

    public function setAnyPromise(bool $isAnyPromise) : void
    {
        $this->isAnyPromise = $isAnyPromise;
    }

    public function isAnyPromise() : bool
    {
        return $this->isAnyPromise;
    }

    public function setAllSettled(bool $isAllSettled) : void
    {
        $this->isAllSettled = $isAllSettled;
    }

    public function isAllSettled() : bool
    {
        return $this->isAllSettled;
    }

    public function isPromiseAll() : bool
    {
        return $this->isPromiseAll;
    }

    public function setPromiseAll(bool $isPromiseAll) : void
    {
        $this->isPromiseAll = $isPromiseAll;
    }

    public function getPromiseCallable() : mixed
    {
        return $this->promiseCallable;
    }

    /**
     * @throws Throwable
     * @return array<PromiseResult>
     */
    private function getResultPromise(Async|Promise $promise) : array
    {
        $results = [];
        $queue = EventQueue::getReturn($promise->getId());

        if (!is_null($queue))
        {
            if ($queue->getStatus() === StatusQueue::FULFILLED)
            {
                $results[] = new PromiseResult($queue->getReturn(), $queue->getStatus());
            }

            if ($queue->getStatus() === StatusQueue::REJECTED)
            {
                $results[] = new PromiseResult($queue->getReturn(), $queue->getStatus());
            }
        }

        return $results;
    }

    /**
     * @throws Throwable
     */
    public function hasCompletedAllPromises() : bool
    {
        $return = false;
        $results = [];

        foreach ($this->waitingPromises as $value)
        {
            $result = $value;

            if (is_callable($value))
            {
                $fiber = new Fiber($value);
                $fiber->start();

                $timeStart = microtime(true);

                while (!$fiber->isTerminated())
                {
                    if ((microtime(true) - $timeStart) > $this->timeDrop)
                    {
                        break;
                    }
                }

                $result = $fiber->getReturn();
            }

            if ($value instanceof Promise || $value instanceof Async)
            {
                $results = array_merge($results, $this->getResultPromise($value));
            }
            elseif ($result instanceof Promise || $result instanceof Async)
            {
                $results = array_merge($results, $this->getResultPromise($result));
            }
        }

        if (count($results) >= count($this->waitingPromises) && $this->isAllSettled())
        {
            $resultPromise = [];

            foreach ($results as $result)
            {
                $resultPromise[] = $result->getResult();
            }

            $this->return = $resultPromise;
            $this->setStatus(StatusQueue::FULFILLED);
            $return = true;
        }

        if (count($results) >= count($this->waitingPromises) && $this->isPromiseAll())
        {
            $haveRejected = false;

            foreach ($results as $result)
            {
                if ($result->getStatus() === StatusQueue::REJECTED)
                {
                    $this->return = $result->getResult();
                    $this->setStatus(StatusQueue::REJECTED);
                    $return = true;
                    $haveRejected = true;
                    break;
                }
            }

            $resultPromise = [];
            if (!$haveRejected)
            {
                foreach ($results as $result)
                {
                    $resultPromise[] = $result->getResult();
                }

                $this->return = $resultPromise;
                $this->setStatus(StatusQueue::FULFILLED);
                $return = true;
            }
        }

        if (count($results) > 0 && $this->isRacePromise())
        {
            $this->return = $results[0]->getResult();

            if ($results[0]->getStatus() === StatusQueue::FULFILLED)
            {
                $this->setStatus(StatusQueue::FULFILLED);
            }

            if ($results[0]->getStatus() === StatusQueue::REJECTED)
            {
                $this->setStatus(StatusQueue::REJECTED);
            }

            $return = true;
        }

        if (count($results) > 0 && $this->isAnyPromise())
        {
            $countRejected = 0;

            foreach ($results as $result)
            {
                if ($result->getStatus() === StatusQueue::FULFILLED)
                {
                    $this->return = $result->getResult();
                    $this->setStatus(StatusQueue::FULFILLED);
                    $return = true;
                    break;
                }
                else
                {
                    $countRejected++;
                }
            }

            if ($countRejected === count($this->waitingPromises))
            {
                $error = new AggregateError(Error::ALL_PROMISES_WERE_REJECTED);
                trigger_error($error->__toString(), E_USER_WARNING);
                
                $this->return = $error->__toString();
                $this->setStatus(StatusQueue::REJECTED);
                $return = true;
            }
        }

        return $return;
    }

}