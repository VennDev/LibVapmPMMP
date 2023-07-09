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
        $this->id = EventQueue::addQueue(
            new Fiber($callable),
            $callable,
            false,
            false,
            0.0
        );

        $queue = EventQueue::getQueue($this->id);

        if (!is_null($queue))
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
                    EventQueue::rejectQueue($this->id, $error->getMessage());
                }
            }
        }
    }

    /**
     * @throws Throwable
     */
    public static function await(callable|Promise|Async $callable) : mixed
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

        if ($callable instanceof Promise || $callable instanceof Async)
        {
            self::awaitPromise($callable, $result);
        }

        if ($result instanceof Promise || $result instanceof Async)
        {
            self::awaitPromise($result, $result);
        }

        return $result;
    }

    /**
     * @throws Throwable
     */
    private static function awaitPromise(Async|Promise $promise, mixed &$result) : void
    {
        $queue = EventQueue::getQueue($promise->getId());

        if (!is_null($queue))
        {
            while ($queue->getStatus() == StatusQueue::PENDING)
            {
                if (
                    $queue->getStatus() == StatusQueue::REJECTED ||
                    $queue->getStatus() == StatusQueue::FULFILLED
                )
                {
                    break;
                }
                self::wait();
            }

            $result = $queue->getReturn();
        }
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