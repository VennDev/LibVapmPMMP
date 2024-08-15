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

use SplQueue;
use const PHP_INT_MAX;

final class MacroTask
{

    private static int $nextId = 0;

    /**
     * @var ?SplQueue
     */
    private static ?SplQueue $tasks = null;

    public static function init(): void
    {
        if (self::$tasks === null) self::$tasks = new SplQueue();
    }

    public static function generateId(): int
    {
        if (self::$nextId >= PHP_INT_MAX) self::$nextId = 0;
        return self::$nextId++;
    }

    public static function removeTask(int $id): void
    {
        if (self::$tasks === null) return;
        $queue = new SplQueue();
        while (!self::$tasks->isEmpty()) {
            /** @var SampleMacro $task */
            $task = self::$tasks->dequeue();
            if ($task->getId() !== $id) $queue->enqueue($task);
        }
        self::$tasks = $queue;
    }

    public static function addTask(SampleMacro $sampleMacro): void
    {
        self::$tasks?->enqueue($sampleMacro);
    }

    public static function getTask(int $id): ?SampleMacro
    {
        while (self::$tasks !== null && !self::$tasks->isEmpty()) {
            /** @var SampleMacro $task */
            $task = self::$tasks->dequeue();
            if ($task->getId() === $id) return $task;
            self::$tasks->enqueue($task);
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

    public static function run(): void
    {
        while (self::$tasks !== null && !self::$tasks->isEmpty()) {
            /** @var SampleMacro $task */
            $task = self::$tasks->dequeue();
            if ($task->checkTimeOut()) {
                $task->run();
                if ($task->isRepeat()) {
                    $task->resetTimeOut();
                    self::$tasks->enqueue($task);
                }
            }
        }
    }

}
