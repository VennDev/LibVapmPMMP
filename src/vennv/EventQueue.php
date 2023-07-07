<?php

namespace vennv;

use Fiber;
use Exception;
use FiberError;
use Throwable;

class EventQueue implements InterfaceEventQueue
{

    private const TIME_OUT = 10;

    private static int $nextId = 0;
    private static array $queues = [];
    private static array $returns = [];

    private static function generateId() : int
    {
        if (self::$nextId >= PHP_INT_MAX) 
        {
            self::$nextId = 0;
        }
        return self::$nextId++;
    }

    public static function getNextId() : int
    {
        if (self::$nextId >= PHP_INT_MAX)
        {
            self::$nextId = 0;
        }
        return self::$nextId + 1;
    }

    public static function isMaxId() : bool
    {
        return self::$nextId >= PHP_INT_MAX;
    }

    /**
     * @throws Throwable
     */
    public static function addQueue(Fiber $fiber, bool $isPromise = false, bool $isPromiseAll = false, float $timeOut = 0.0) : int
    {
        $id = self::generateId();
        self::$queues[$id] = new Queue($id, $fiber, $timeOut, StatusQueue::PENDING, $isPromise, $isPromiseAll);
        return $id;
    }

    public static function getQueue(int $id) : ?Queue
    {
        return self::$queues[$id] ?? null;
    }

    public static function getReturn(int $id) : mixed
    {
        return self::$returns[$id] ?? null;
    }

    public static function unsetReturn(int $id) : void
    {
        unset(self::$returns[$id]);
    }

    private static function getResultFiber(Fiber $fiber) : mixed
    {
        try
        {
            $result = $fiber->getReturn();
        }
        catch (FiberError $error)
        {
            $result = $error->getMessage();
        }
        return $result;
    }

    /**
     * @throws Throwable
     */
    private static function doResult(int $id) : void
    {
        $queue = self::getQueue($id);
        if (!is_null($queue))
        {
            $status = $queue->getStatus();
            $result = $queue->getReturn();

            switch ($status)
            {
                case StatusQueue::FULFILLED:
                    $queue->useCallableResolve($result);
                    break;
                case StatusQueue::REJECTED:
                    $queue->useCallableReject($result);
                    break;
                case StatusQueue::PENDING:
                    throw new EventQueueError(
                        str_replace("%id%", $id, Error::QUEUE_STILL_PENDING)
                    );
            }

            self::$returns[$id] = $queue;
            unset(self::$queues[$id]);
        }
        else
        {
            throw new EventQueueError(
                str_replace("%id%", $id, Error::QUEUE_NOT_FOUND)
            );
        }
    }

    /**
     * @throws Throwable
     */
    public static function runQueue(int $id) : void
    {
        $queue = self::getQueue($id);
        if ($queue !== null)
        {
            $fiber = $queue->getFiber();
            if (!$fiber->isStarted())
            {
                try
                {
                    $fiber->start();
                }
                catch (Exception | Throwable $error)
                {
                    EventQueue::rejectQueue($id, $error->getMessage());
                }
            }
        }
    }

    /**
     * @throws Throwable
     */
    private static function queueFulfilled(int $id) : bool
    {
        $queue = self::getQueue($id);
        if ($queue !== null)
        {
            $status = $queue->getStatus();
            return $status === StatusQueue::FULFILLED || $status === StatusQueue::REJECTED;
        }
        return false;
    }

    /**
     * @throws Throwable
     */
    public static function rejectQueue(int $id, mixed $result) : void
    {
        $queue = self::getQueue($id);
        if (!is_null($queue))
        {
            $queue->setStatus(StatusQueue::REJECTED);
            $queue->setReturn($result);
            self::doResult($id);
        }
    }

    /**
     * @throws Throwable
     */
    public static function fulfillQueue(int $id, mixed $result) : void
    {
        $queue = self::getQueue($id);
        if (!is_null($queue))
        {
            $queue->setStatus(StatusQueue::FULFILLED);
            $queue->setReturn($result);
            self::doResult($id);
        }
    }

    /**
     * @throws Throwable
     */
    public static function fulfillPromise(int $id) : void
    {
        $queue = self::getQueue($id);
        if (!is_null($queue))
        {
            self::doResult($id);
        }
    }

    /**
     * @throws Throwable
     */
    private static function checkStatus(int $id) : void
    {
        $queue = self::getQueue($id);

        if (!is_null($queue))
        {
            $id = $queue->getId();
            $fiber = $queue->getFiber();
            $isPromise = $queue->isPromise();

            if ($isPromise)
            {
                $resolve = function($result) use ($id)
                {
                    Promise::resolve($id, $result);
                };

                $reject = function($result) use ($id)
                {
                    Promise::reject($id, $result);
                };
            }

            if (!$fiber->isStarted() && !$isPromise)
            {
                try
                {
                    $fiber->start();
                }
                catch (Exception | Throwable $error)
                {
                    self::rejectQueue($id, $error->getMessage());
                }
            }

            if (!$fiber->isStarted() && $isPromise)
            {
                try
                {
                    $fiber->start($resolve, $reject);
                }
                catch (Exception | Throwable $error)
                {
                    self::rejectQueue($id, $error->getMessage());
                }
            }

            if ($fiber->isSuspended())
            {
                try
                {
                    $fiber->resume();
                }
                catch (Exception | Throwable $error)
                {
                    self::rejectQueue($id, $error->getMessage());
                }
            }

            if ($fiber->isTerminated())
            {
                if (!$isPromise)
                {
                    self::fulfillQueue($id, self::getResultFiber($fiber));
                }
                elseif ($queue->getStatus() !== StatusQueue::PENDING)
                {
                    self::fulfillPromise($id);
                }

                if ($queue->isPromiseAll())
                {
                    if ($queue->hasCompletedAllPromise())
                    {
                        self::fulfillPromise($id);
                    }
                }
            }
        }
        else
        {
            throw new EventQueueError(
                str_replace("%id%", $id, Error::QUEUE_NOT_FOUND)
            );
        }
    }

    private static function shouldCheckStatus(Queue $queue, float $addTime = 0.0) : bool
    {
        $timeOut = $queue->getTimeOut();
        $timeStart = $queue->getTimeStart();
        $timeNow = microtime(true);

        $diff = $timeNow - $timeStart;

        return $diff >= $timeOut + $addTime;
    }

    /**
     * @throws Throwable
     */
    private static function run() : void
    {
        foreach (self::$queues as $id => $queue)
        {
            if ($queue instanceof Queue)
            {
                
                if (self::shouldCheckStatus($queue))
                {
                    self::checkStatus($id);
                }

                // If the queue is still pending after 10 seconds of timeout, reject it.
                // If you encounter this problem, your current promise trick is too bad.
                if (self::shouldCheckStatus($queue, self::TIME_OUT))
                {
                    self::rejectQueue(
                        $id, str_replace("%id%", $id, Error::QUEUE_IS_TIMEOUT)
                    );
                }
            }
        }
        foreach (self::$returns as $id => $queue)
        {
            $canDrop = $queue->canDrop();

            if ($canDrop)
            {
                unset(self::$returns[$id]);
            }
        }
    }

    /**
     * @throws Throwable
     */
    protected static function runSingleJob() : void
    {
        while (count(self::$queues) > 0)
        {
            self::run();
        }
    }

    /**
     * @throws Throwable
     */
    protected static function runMultiJobs() : void
    {
        self::run();
    }

}