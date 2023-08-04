<?php

/**
 * Vapm - A library for PHP about Async, Promise, Coroutine, GreenThread,
 *      Thread and other non-blocking methods. The method is based on Fibers &
 *      Generator & Processes, requires you to have php version from >= 8.1
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

declare(strict_types = 1);

namespace vennv\vapm;

use Closure;
use ReflectionException;
use SplQueue;
use Generator;
use Throwable;
use function call_user_func;

interface CoroutineGenInterface {

    /**
     * @param mixed ...$coroutines
     * @return void
     *
     * This is a blocking function that runs all the coroutines passed to it.
     */
    public static function runBlocking(mixed ...$coroutines) : void;

    /**
     * @param callable $callback
     * @param int $times
     * @return Closure
     *
     * This is a generator that runs a callback function a specified amount of times.
     */
    public static function repeat(callable $callback, int $times) : Closure;

    /**
     * @param int $milliseconds
     * @return Generator
     *
     * This is a generator that yields for a specified amount of milliseconds.
     */
    public static function delay(int $milliseconds) : Generator;

    /**
     * @param mixed ...$callback
     * @return CoroutineScope
     *
     * This is a generator that runs a callback function.
     */
    public static function launch(mixed ...$callback) : CoroutineScope;

}

final class CoroutineGen implements CoroutineGenInterface {

    protected static ?SplQueue $taskQueue = null;

    /**
     * @throws Throwable
     * @param mixed ...$coroutines
     * @return void
     */
    public static function runBlocking(mixed ...$coroutines) : void {
        foreach ($coroutines as $coroutine) {
            if (is_callable($coroutine)) {
                $coroutine = call_user_func($coroutine);
            }

            if ($coroutine instanceof CoroutineScope) {
                self::schedule($coroutine);
            } else if ($coroutine instanceof Generator) {
                self::schedule(new ChildCoroutine($coroutine));
            } else {
                call_user_func(fn() => $coroutine);
            }
        }

        self::run();
    }

    /**
     * @param mixed ...$coroutines
     * @return Closure
     */
    private static function processCoroutine(mixed ...$coroutines) : Closure {
        return function () use ($coroutines) : void {
            foreach ($coroutines as $coroutine) {
                if ($coroutine instanceof CoroutineScope) {
                    self::schedule($coroutine);
                } else if (is_callable($coroutine)) {
                    $coroutine = call_user_func($coroutine);
                }

                if (!$coroutine instanceof Generator) {
                    call_user_func(fn() => $coroutine);
                } else {
                    self::schedule(new ChildCoroutine($coroutine));
                }
            }

            self::run();
        };
    }

    public static function repeat(callable $callback, int $times) : Closure {
        for ($i = 0; $i <= $times; $i++) {
            if (call_user_func($callback) instanceof Generator) {
                $callback = self::processCoroutine($callback);
            }
        }

        return fn() => null;
    }

    public static function delay(int $milliseconds) : Generator {
        for ($i = 0; $i < GeneratorManager::calculateSeconds($milliseconds); $i++) {
            yield;
        }
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public static function launch(mixed ...$callback) : CoroutineScope {
        $coroutine = new CoroutineScope();
        $coroutine->launch(...$callback);

        return $coroutine;
    }

    private static function schedule(ChildCoroutine|CoroutineScope $childCoroutine) : void {
        if (self::$taskQueue === null) {
            self::$taskQueue = new SplQueue();
        }

        self::$taskQueue->enqueue($childCoroutine);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    private static function run() : void {
        if (self::$taskQueue !== null) {
            while (!self::$taskQueue->isEmpty()) {
                $coroutine = self::$taskQueue->dequeue();

                if ($coroutine instanceof ChildCoroutine) {
                    $coroutine->run();

                    if (!$coroutine->isFinished()) {
                        self::schedule($coroutine);
                    }
                }

                if ($coroutine instanceof CoroutineScope) {
                    $coroutine->run();

                    if (!$coroutine->isFinished()) {
                        self::schedule($coroutine);
                    }
                }
            }
        }
    }

}