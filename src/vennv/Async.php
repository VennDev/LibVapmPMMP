<?php

namespace vennv;

use Fiber;
use Throwable;
use Exception;

final class Async implements InterfaceAsync
{

    private int $id;

    /**
     * @throws Throwable
     */
    public function __construct(callable $callable)
    {
        $this->id = EventQueue::addQueue(new Fiber($callable));

        $queue = EventQueue::getQueue($this->id);
        $fiber = $queue->getFiber();

        if (!$fiber->isStarted())
        {
            try
            {
                $fiber->start();
            }
            catch (Exception | Throwable $error)
            {
                EventQueue::rejectQueue($this->id, $error->getMessage());
            }
        }
    }

    /**
     * @throws Throwable
     */
    public static function await(mixed $callable) : mixed
    {
        $result = $callable;

        if (is_callable($callable))
        {
            $fiber = new Fiber($callable);
            $fiber->start();

            if (!$fiber->isTerminated())
            {
                self::wait();
            }

            $result = $fiber->getReturn();
        }

        if (
            $callable instanceof Promise || 
            $callable instanceof Async ||
            $result instanceof Promise || 
            $result instanceof Async
        )
        {
            $queue = EventQueue::getQueue($callable->getId());

            if (!is_null($queue))
            {
                while ($queue->getStatus() === StatusQueue::PENDING)
                {
                    if (
                        $queue->getStatus() === StatusQueue::REJECTED ||
                        $queue->getStatus() === StatusQueue::FULFILLED
                    )
                    {
                        break;
                    }
                    self::wait();
                }

                $result = $queue->getReturn();
            }
        }

        return $result;
    }

    /**
     * @throws Throwable
     */
    public static function wait() : void
    {
        $fiber = Fiber::getCurrent();
        if (!is_null($fiber))
        {
            Fiber::suspend();
        }
    }

    public function getId() : int
    {
        return $this->id;
    }

}