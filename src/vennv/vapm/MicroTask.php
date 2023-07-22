<?php

/**
 * Vapm and a brief idea of what it does.>
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

declare(strict_types = 1);

namespace vennv\vapm;

use Throwable;
use function microtime;

final class MicroTask
{

    /**
     * @var array<int, Promise>
     */
    private static array $tasks = [];

    public static function addTask(int $id, Promise $promise): void
    {
        self::$tasks[$id] = $promise;
    }

    public static function removeTask(int $id): void
    {
        unset(self::$tasks[$id]);
    }

    public static function getTask(int $id): ?Promise
    {
        return self::$tasks[$id] ?? null;
    }

    /**
     * @return array<int, Promise>
     */
    public static function getTasks(): array
    {
        return self::$tasks;
    }

    /**
     * @throws Throwable
     */
    public static function run(): void
    {
        foreach (self::$tasks as $id => $promise)
        {
            $promise->useCallbacks();
            $promise->setTimeEnd(microtime(true));
            
            EventLoop::addReturn($promise);

            self::removeTask($id);
        }
    }

}