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

use Throwable;
use SplQueue;
use function microtime;

final class MicroTask
{

    /**
     * @var ?SplQueue
     */
    private static ?SplQueue $tasks = null;

    public static function init(): void
    {
        if (self::$tasks === null) self::$tasks = new SplQueue();
    }

    public static function addTask(Promise $promise): void
    {
        self::$tasks?->enqueue($promise);
    }

    public static function getTask(int $id): ?Promise
    {
        while (self::$tasks !== null && !self::$tasks->isEmpty()) {
            /** @var Promise $promise */
            $promise = self::$tasks->dequeue();
            if ($promise->getId() === $id) return $promise;
            self::$tasks->enqueue($promise);
        }
        return null;
    }

    /**
     * @return ?SplQueue
     */
    public static function getTasks(): ?SplQueue
    {
        return self::$tasks;
    }

    /**
     * @throws Throwable
     */
    public static function run(): void
    {
        while (self::$tasks !== null && !self::$tasks->isEmpty()) {
            /** @var Promise $promise */
            $promise = self::$tasks->dequeue();
            $promise->useCallbacks();
            $promise->setTimeEnd(microtime(true));
            EventLoop::addReturn($promise);
        }
    }

}
