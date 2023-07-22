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

use const PHP_INT_MAX;

final class MacroTask
{

    private static int $nextId = 0;

    /**
     * @var array<int, SampleMacro>
     */
    private static array $tasks = [];

    public static function generateId(): int
    {
        if (self::$nextId >= PHP_INT_MAX)
        {
            self::$nextId = 0;
        }

        return self::$nextId++;
    }

    public static function addTask(SampleMacro $sampleMacro): void
    {
        self::$tasks[$sampleMacro->getId()] = $sampleMacro;
    }

    public static function removeTask(SampleMacro $sampleMacro): void
    {
        $id = $sampleMacro->getId();

        if (isset(self::$tasks[$id]))
        {
            unset(self::$tasks[$id]);
        }
    }

    public static function getTask(int $id): ?SampleMacro
    {
        return self::$tasks[$id] ?? null;
    }

    /**
     * @return array<int, SampleMacro>
     */
    public static function getTasks(): array
    {
        return self::$tasks;
    }

    public static function run(): void
    {
        foreach (self::$tasks as $task)
        {
            if ($task->checkTimeOut())
            {
                $task->run();

                if (!$task->isRepeat())
                {
                    self::removeTask($task);
                }
            }
        }
    }

}