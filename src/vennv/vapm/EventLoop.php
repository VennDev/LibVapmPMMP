<?php

/*
 * Copyright (c) 2023 VennV, CC0 1.0 Universal
 *
 * CREATIVE COMMONS CORPORATION IS NOT A LAW FIRM AND DOES NOT PROVIDE
 * LEGAL SERVICES. DISTRIBUTION OF THIS DOCUMENT DOES NOT CREATE AN
 * ATTORNEY-CLIENT RELATIONSHIP. CREATIVE COMMONS PROVIDES THIS
 * INFORMATION ON AN "AS-IS" BASIS. CREATIVE COMMONS MAKES NO WARRANTIES
 * REGARDING THE USE OF THIS DOCUMENT OR THE INFORMATION OR WORKS
 * PROVIDED HEREUNDER, AND DISCLAIMS LIABILITY FOR DAMAGES RESULTING FROM
 * THE USE OF THIS DOCUMENT OR THE INFORMATION OR WORKS PROVIDED
 * HEREUNDER.
 */

declare(strict_types = 1);

namespace vennv\vapm;

use Throwable;
use function count;
use const PHP_INT_MAX;

interface EventLoopInterface
{

    public static function generateId(): int;

    public static function addQueue(Promise $promise): void;

    public static function removeQueue(int $id): void;

    public static function getQueue(int $id): ?Promise;

    /**
     * @return array<int, Promise>
     */
    public static function getQueues(): array;

    public static function addReturn(Promise $promise): void;

    public static function removeReturn(int $id): void;

    public static function getReturn(int $id): ?Promise;

    /**
     * @return array<int, Promise>
     */
    public static function getReturns(): array;

}

class EventLoop implements EventLoopInterface
{

    private static int $nextId = 0;

    /**
     * @var array<int, Promise>
     */
    private static array $queues = [];

    /**
     * @var array<int, Promise>
     */
    private static array $returns = [];

    public static function generateId(): int
    {
        if (self::$nextId >= PHP_INT_MAX)
        {
            self::$nextId = 0;
        }

        return self::$nextId++;
    }

    public static function addQueue(Promise $promise): void
    {
        $id = $promise->getId();

        if (!isset(self::$queues[$id]))
        {
            self::$queues[$id] = $promise;
        }
    }

    public static function removeQueue(int $id): void
    {
        unset(self::$queues[$id]);
    }

    public static function getQueue(int $id): ?Promise
    {
        return self::$queues[$id] ?? null;
    }

    /**
     * @return array<int, Promise>
     */
    public static function getQueues(): array
    {
        return self::$queues;
    }

    public static function addReturn(Promise $promise): void
    {
        $id = $promise->getId();

        if (!isset(self::$returns[$id]))
        {
            self::$returns[$id] = $promise;
        }
    }

    public static function removeReturn(int $id): void
    {
        unset(self::$returns[$id]);
    }

    public static function getReturn(int $id): ?Promise
    {
        return self::$returns[$id] ?? null;
    }

    /**
     * @return array<int, Promise>
     */
    public static function getReturns(): array
    {
        return self::$returns;
    }

    private static function clearGarbage(): void
    {
        foreach (self::$returns as $id => $promise)
        {
            if ($promise->canDrop())
            {
                self::removeReturn($id);
            }
        }
    }

    /**
     * @throws Throwable
     */
    protected static function run(): void
    {
        if (count(GreenThread::getFibers()) > 0)
        {
            GreenThread::run();
        }

        foreach (self::$queues as $id => $promise)
        {
            $fiber = $promise->getFiber();
            
            if ($fiber->isSuspended())
            {
                $fiber->resume();
            }
            elseif (!$fiber->isTerminated())
            {
                FiberManager::wait();
            }
            
            if ($fiber->isTerminated() && ($promise->getStatus() !== StatusPromise::PENDING || $promise->isJustGetResult()))
            {
                MicroTask::addTask($id, $promise);
                self::removeQueue($id);
            }
        }

        if (count(MicroTask::getTasks()) > 0)
        {
            MicroTask::run();
        }

        if (count(MacroTask::getTasks()) > 0)
        {
            MacroTask::run();
        }

        self::clearGarbage();
    }

    /**
     * @throws Throwable
     */
    protected static function runSingle(): void
    {
        while (count(self::$queues) > 0 || count(MicroTask::getTasks()) > 0 || count(MacroTask::getTasks()) > 0 || count(GreenThread::getFibers()) > 0)
        {
            self::run();
        }
    }

}