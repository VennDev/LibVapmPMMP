<?php

namespace vennv;

use Exception;
use Fiber;
use Throwable;

final class Queue
{

    private const MAIN_QUEUE = "Main";

    private float $timeStart;
    private float $timeDrop;
    private mixed $return;
    private array $callableResolve;
    private array $callableReject;
    private mixed $returnResolve;
    private mixed $returnReject;
    private array $waitingPromises = [];
    private bool $isRacePromise = false;
    private bool $isAnyPromise = false;
    private bool $isAllSettled = false;

    public function __construct(
        private readonly int $id,
        private readonly Fiber $fiber,
        private readonly float $timeOut,
        private StatusQueue $status,
        private readonly bool $isPromise,
        private readonly bool $isPromiseAll = false
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
        while (!$fiber->isTerminated())
        {
            if ($fiber->isTerminated())
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

    private function checkStatus(string $callableFc, string $return) : void
    {
        while (count($this->{"$callableFc"}) > 0)
        {
            $firstCheck = false;
            $cancel = false;

            foreach ($this->{"$callableFc"} as $id => $callable)
            {
                if ($this->{"$return"} === null) 
                {
                    $cancel = true;
                    break;
                }
                if (
                    $id !== self::MAIN_QUEUE && 
                    $this->{"$return"} instanceof Promise && 
                    !$firstCheck
                )
                {
                    EventQueue::getQueue($this->{"$return"}->getId())->setCallableResolve($callable);
                    $firstCheck = true;
                }
                elseif (
                    $id !== self::MAIN_QUEUE && 
                    $this->{"$return"} instanceof Promise
                )
                {
                    EventQueue::getQueue($this->{"$return"}->getId())->then($callable);
                    unset($this->{"$callableFc"}[$id]);
                    continue;
                }
                if (count($this->{"$callableFc"}) === 1)
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
            
            try 
            {
                $fiber->start();
            } 
            catch (Throwable | Exception $error) 
            {
                echo $error->getMessage();
            }

            unset($this->callableResolve[self::MAIN_QUEUE]);

            $this->returnResolve = $this->getResult($fiber);

            $this->checkStatus("callableResolve", "returnResolve");
        }
    }

    public function setCallableResolve(mixed $callableResolve) : Queue
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
            
            try 
            {
                $fiber->start();
            } 
            catch (Throwable | Exception $error) 
            {
                echo $error->getMessage();
            }

            $this->returnReject = $this->getResult($fiber);

            $this->checkStatus("callableReject", "returnReject");
        }
    }

    public function setCallableReject(mixed $callableReject) : Queue
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

    public function getWaitingPromises() : array
    {
        return $this->waitingPromises;
    }

    public function setWaitingPromises(array $waitingPromises) : void
    {
        $this->waitingPromises = $waitingPromises;
    }

    public function isPromiseAll() : bool
    {
        return $this->isPromiseAll;
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

    /**
     * @throws Throwable
     */
    public function hasCompletedAllPromise() : bool
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

                while (!$fiber->isTerminated())
                {
                    if ($fiber->isTerminated())
                    {
                        break;
                    }
                }

                $result = $fiber->getReturn();
            }

            if (
                $value instanceof Promise || 
                $value instanceof Async ||
                $result instanceof Promise || 
                $result instanceof Async
            )
            {
                $queue = EventQueue::getReturn($value->getId());

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
            }
        }

        if (
            count($results) === count($this->waitingPromises) &&
            $this->isPromiseAll() &&
            $this->isAllSettled() &&
            !$this->isRacePromise() &&
            !$this->isAnyPromise()
        )
        {
            foreach ($results as $result)
            {
                $this->return[] = $result->getResult();
            }
            $this->setStatus(StatusQueue::FULFILLED);
            $return = true;
        }

        if (
            count($results) === count($this->waitingPromises) &&
            $this->isPromiseAll() &&
            !$this->isAllSettled() &&
            !$this->isRacePromise() &&
            !$this->isAnyPromise()
        )
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

            if (!$haveRejected)
            {
                foreach ($results as $result)
                {
                    $this->return[] = $result->getResult();
                }
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
                
                $this->return = $error->__toString();
                $this->setStatus(StatusQueue::REJECTED);
                $return = true;
            }
        }

        return $return;
    }

}