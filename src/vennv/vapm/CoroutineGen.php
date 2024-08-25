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

use Closure;
use ReflectionException;
use SplQueue;
use Generator;
use Throwable;
use function call_user_func;

interface CoroutineGenInterface
{

    /**
     * @return SplQueue|null
     *
     * This function returns the task queue.
     */
    public static function getTaskQueue(): ?SplQueue;

    /**
     * @param mixed ...$coroutines
     * @return void
     *
     * This is a blocking function that runs all the coroutines passed to it.
     */
    public static function runNonBlocking(mixed ...$coroutines): void;

    /**
     * @param mixed ...$coroutines
     * @return void
     *
     * This is a blocking function that runs all the coroutines passed to it.
     */
    public static function runBlocking(mixed ...$coroutines): void;

    /**
     * @param callable $callback
     * @param int $times
     * @return Closure
     *
     * This is a generator that runs a callback function a specified amount of times.
     */
    public static function repeat(callable $callback, int $times): Closure;

    /**
     * @param int $milliseconds
     * @return Generator
     *
     * This is a generator that yields for a specified amount of milliseconds.
     */
    public static function delay(int $milliseconds): Generator;

    /**
     * @return void
     *
     * This function runs the task queue.
     */
    public static function run(): void;

}

final class CoroutineGen implements CoroutineGenInterface
{

    private static ?SplQueue $taskQueue = null;

    public static function getTaskQueue(): ?SplQueue
    {
        return self::$taskQueue;
    }

    /**
     * @param mixed ...$coroutines
     * @return void
     * @throws Throwable
     */
    public static function runNonBlocking(mixed ...$coroutines): void
    {
        System::init();
        if (self::$taskQueue === null) self::$taskQueue = new SplQueue();

        foreach ($coroutines as $coroutine) {
            if (is_callable($coroutine)) $coroutine = call_user_func($coroutine);

            if ($coroutine instanceof Generator) {
                self::schedule(new ChildCoroutine($coroutine));
            } else {
                call_user_func(fn() => $coroutine);
            }
        }

        self::run();
    }

    /**
     * @param mixed ...$coroutines
     * @return void
     * @throws Throwable
     */
    public static function runBlocking(mixed ...$coroutines): void
    {
        self::runNonBlocking(...$coroutines);
        while (self::$taskQueue?->isEmpty() === false) self::run();
    }

    /**
     * @param mixed ...$coroutines
     * @return Closure
     */
    private static function processCoroutine(mixed ...$coroutines): Closure
    {
        return function () use ($coroutines): void {
            foreach ($coroutines as $coroutine) {
                if (is_callable($coroutine)) {
                    $coroutine = call_user_func($coroutine);
                }

                !$coroutine instanceof Generator ? call_user_func(fn() => $coroutine) : self::schedule(new ChildCoroutine($coroutine));
            }

            self::run();
        };
    }

    public static function repeat(callable $callback, int $times): Closure
    {
        for ($i = 0; $i <= $times; $i++) if (call_user_func($callback) instanceof Generator) $callback = self::processCoroutine($callback);
        return fn() => null;
    }

    public static function delay(int $milliseconds): Generator
    {
        for ($i = 0; $i < GeneratorManager::calculateSeconds($milliseconds); $i++) yield;
    }

    private static function schedule(ChildCoroutine $childCoroutine): void
    {
        self::$taskQueue?->enqueue($childCoroutine);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public static function run(): void
    {
        $i = 0;
        while (self::$taskQueue?->isEmpty() === false) {
            if ($i++ >= 3) break;
            $coroutine = self::$taskQueue->dequeue();
            if ($coroutine instanceof ChildCoroutine) {
                $coroutine->run();
                if (!$coroutine->isFinished()) self::schedule($coroutine);
            }
        }
    }

}
