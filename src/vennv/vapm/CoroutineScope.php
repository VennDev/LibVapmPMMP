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

use Generator;
use ReflectionException;
use SplQueue;
use Throwable;
use function call_user_func;
use function is_callable;

interface CoroutineScopeInterface
{

    /**
     * @return bool
     *
     * This function checks if the coroutine has finished.
     */
    public function isFinished(): bool;

    /**
     * @return bool
     *
     * This function checks if the coroutine has been cancelled.
     */
    public function isCancelled(): bool;

    /**
     * This function cancels the coroutine.
     */
    public function cancel(): void;

    /**
     * @param mixed ...$callbacks
     * @throws ReflectionException
     * @throws Throwable
     *
     * This function launches a coroutine.
     */
    public function launch(mixed ...$callbacks): void;

    /**
     * This function runs the coroutine.
     */
    public function run(): Async;

}

final class CoroutineScope implements CoroutineScopeInterface
{

    protected static ?SplQueue $taskQueue = null;

    protected static bool $cancelled = false;

    protected static bool $finished = false;

    protected static string $dispatcher;

    public function __construct(string $dispatcher = Dispatchers::DEFAULT)
    {
        self::$dispatcher = $dispatcher;
    }

    public function isFinished(): bool
    {
        return self::$finished;
    }

    public function isCancelled(): bool
    {
        return self::$cancelled;
    }

    public function cancel(): void
    {
        self::$cancelled = true;
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function launch(mixed ...$callbacks): void
    {
        foreach ($callbacks as $callback) {
            if ($callback instanceof CoroutineScope) {
                self::schedule($callback);
            } else if (is_callable($callback)) {
                if (self::$dispatcher === Dispatchers::IO) {
                    $thread = new CoroutineThread($callback);
                    $thread->start();
                }

                if (self::$dispatcher === Dispatchers::DEFAULT) $callback = call_user_func($callback);
            } else {
                $callback = fn() => $callback;
            }

            if ($callback instanceof Generator) self::schedule(new ChildCoroutine($callback));
        }
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function run(): Async
    {
        return new Async(function (): void {
            if (self::$taskQueue?->isEmpty() === false && !self::$cancelled) {
                $coroutine = self::$taskQueue->dequeue();

                if ($coroutine instanceof ChildCoroutine) {
                    $coroutine->run();

                    if (!$coroutine->isFinished()) self::schedule($coroutine);
                }

                if ($coroutine instanceof CoroutineScope) {
                    Async::await($coroutine->run());

                    if (!$coroutine->isFinished()) self::schedule($coroutine);
                }
            } else {
                self::$finished = true;
            }
        });
    }

    private static function schedule(ChildCoroutine|CoroutineScope $childCoroutine): void
    {
        if (self::$taskQueue === null) self::$taskQueue = new SplQueue();
        self::$taskQueue->enqueue($childCoroutine);
    }

}