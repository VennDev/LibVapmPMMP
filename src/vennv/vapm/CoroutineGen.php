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

use SplQueue;
use Generator;

interface CoroutineGenInterface
{

    /**
     * @param Generator|callable ...$coroutines
     * @return void
     */
    public static function runBlocking(Generator|callable ...$coroutines): void;

    public static function launch(callable $callback): Generator;

}

final class CoroutineGen implements CoroutineGenInterface
{

    protected static int $maxTaskId = 0;
    
    protected static ?SplQueue $taskQueue = null;

    /**
     * @param Generator|callable ...$coroutines
     * @return void
     */
    public static function runBlocking(Generator|callable ...$coroutines): void
    {
        if (self::$taskQueue === null) 
        {
            self::$taskQueue = new SplQueue();
        }

        foreach ($coroutines as $coroutine)
        {
            if (is_callable($coroutine))
            {
                $coroutine = call_user_func($coroutine);
            }

            if ($coroutine instanceof Generator)
            {
                $tid = ++self::$maxTaskId;
                self::schedule(new ChildCoroutine($tid, $coroutine));
            }
        }

        self::run();
    }

    public static function launch(callable $callback): Generator
    {
        return yield $callback;
    }

    private static function schedule(ChildCoroutine $childCoroutine): void
    {
        self::$taskQueue?->enqueue($childCoroutine);
    }

    private static function run(): void
    {
        if (self::$taskQueue !== null)
        {
            while (!self::$taskQueue->isEmpty())
            {
                /**
                 * @var ChildCoroutine $childCoroutine
                 */
                $childCoroutine = self::$taskQueue->dequeue();
                $childCoroutine->run();

                if (!$childCoroutine->isFinished())
                {
                    self::schedule($childCoroutine);
                }
            }
        }
    }

}