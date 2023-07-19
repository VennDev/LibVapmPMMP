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