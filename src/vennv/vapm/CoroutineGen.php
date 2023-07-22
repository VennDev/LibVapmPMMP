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