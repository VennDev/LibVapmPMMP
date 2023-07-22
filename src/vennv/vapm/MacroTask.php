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