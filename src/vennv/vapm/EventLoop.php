<?php

/**
 * Vapm - A library support for PHP about Async, Promise, Coroutine, Thread, GreenThread
 *          and other non-blocking methods. The library also includes some Javascript packages
 *          such as Express. The method is based on Fibers & Generator & Processes, requires
 *          you to have php version from >= 8.1
 *
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

declare(strict_types=1);

namespace vennv\vapm;

use Generator;
use SplObjectStorage;
use Throwable;
use function count;
use const PHP_INT_MAX;

interface EventLoopInterface
{

    public static function init(): void;

    public static function generateId(): int;

    public static function addQueue(Promise $promise): void;

    public static function removeQueue(int $id): void;

    public static function isQueue(int $id): bool;

    public static function getQueue(int $id): ?Promise;

    /**
     * @return Generator
     */
    public static function getQueues(): Generator;

    public static function addReturn(Promise $promise): void;

    public static function removeReturn(int $id): void;

    public static function isReturn(int $id): bool;

    public static function getReturn(int $id): ?Promise;

    /**
     * @return Generator
     */
    public static function getReturns(): Generator;

}

class EventLoop implements EventLoopInterface
{

    protected static int $limit = 10;

    protected static int $nextId = 0;

    /**
     * @var SplObjectStorage
     */
    protected static SplObjectStorage $queues;

    /**
     * @var array<int, Promise>
     */
    protected static array $returns = [];

    public static function init(): void
    {
        if (!isset(self::$queues)) self::$queues = new SplObjectStorage();
    }

    public static function generateId(): int
    {
        if (self::$nextId >= PHP_INT_MAX) self::$nextId = 0;
        return self::$nextId++;
    }

    public static function addQueue(Promise $promise): void
    {
        if (!self::getQueue($promise->getId())) self::$queues->offsetSet($promise, $promise->getId());
    }

    public static function removeQueue(int $id): void
    {
        foreach (self::$queues as $promise) {
            if ($promise instanceof Promise && $promise->getId() === $id) {
                self::$queues->offsetUnset($promise);
                break;
            }
        }
    }

    public static function isQueue(int $id): bool
    {
        /* @var Promise $promise */
        foreach (self::$queues as $promise) if ($promise instanceof Promise && $promise->getId() === $id) return true;
        return false;
    }

    public static function getQueue(int $id): ?Promise
    {
        /* @var Promise $promise */
        foreach (self::$queues as $promise) if ($promise instanceof Promise && $promise->getId() === $id) return $promise;
        return null;
    }

    /**
     * @return Generator
     */
    public static function getQueues(): Generator
    {
        foreach (self::$queues as $promise) {
            yield $promise;
        }
    }

    public static function addReturn(Promise $promise): void
    {
        if (!isset(self::$returns[$promise->getId()])) self::$returns[$promise->getId()] = $promise;
    }

    public static function isReturn(int $id): bool
    {
        return isset(self::$returns[$id]);
    }

    public static function removeReturn(int $id): void
    {
        if (self::isReturn($id)) unset(self::$returns[$id]);
    }

    public static function getReturn(int $id): ?Promise
    {
        return self::$returns[$id] ?? null;
    }

    /**
     * @return Generator
     */
    public static function getReturns(): Generator
    {
        foreach (self::$returns as $id => $promise) {
            yield $id => $promise;
        }
    }

    /**
     * @throws Throwable
     */
    private static function clearGarbage(): void
    {
        foreach (self::getReturns() as $id => $promise) if ($promise instanceof Promise && $promise->canDrop()) unset(self::$returns[$id]);
    }

    /**
     * @throws Throwable
     */
    protected static function run(): void
    {
        $i = 0;

        /**
         * @var Promise $promise
         */
        foreach (self::getQueues() as $promise) {
            if ($i++ >= self::$limit) break;

            $id = $promise->getId();
            $fiber = $promise->getFiber();

            if ($fiber->isSuspended()) {
                $fiber->resume();
            } else if (!$fiber->isTerminated()) {
                FiberManager::wait();
            }

            if ($fiber->isTerminated() && ($promise->getStatus() !== StatusPromise::PENDING || $promise->isJustGetResult())) {
                try {
                    if ($promise->isJustGetResult()) $promise->setResult($fiber->getReturn());
                } catch (Throwable $e) {
                    echo $e->getMessage();
                }
                MicroTask::addTask($id, $promise);
                self::$queues->offsetUnset($promise); // Remove from queue
            } else {
                self::$queues->detach($promise); // Remove from queue
                self::$queues->attach($promise, $id); // Add to queue again
            }
        }

        if (count(MicroTask::getTasks()) > 0) MicroTask::run();
        if (count(MacroTask::getTasks()) > 0) MacroTask::run();

        self::clearGarbage();
    }

    /**
     * @throws Throwable
     */
    protected static function runSingle(): void
    {
        self::$limit = min((int)((count(self::$queues) / 2) + 1), 100); // Limit 100 promises per loop
        while (count(self::$queues) > 0 || count(MicroTask::getTasks()) > 0 || count(MacroTask::getTasks()) > 0) self::run();
    }

}
