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

use SplQueue;
use Generator;

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

    private static function schedule(ChildCoroutine $ChildCoroutine): void
    {
        self::$taskQueue?->enqueue($ChildCoroutine);
    }

    private static function run(): void
    {
        if (self::$taskQueue !== null)
        {
            while (!self::$taskQueue->isEmpty())
            {
                /**
                 * @var ChildCoroutine $ChildCoroutine
                 */
                $ChildCoroutine = self::$taskQueue->dequeue();
                $ChildCoroutine->run();

                if ($ChildCoroutine->isFinished())
                {
                    //TODO: Remove from queue
                }
                else
                {
                    self::schedule($ChildCoroutine);
                }
            }
        }
    }

}