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
        foreach (GeneratorManager::getFromArray(self::$tasks) as $task)
        {
            /** @var SampleMacro $task */
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