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

use Fiber;
use Throwable;

final class GreenThread implements GreenThreadInterface
{

    /**
     * @var array<int, string|int>
     */
    private static array $names = [];

    /**
     * @var array<int, Fiber>
     */
    private static array $fibers = [];

    /**
     * @var array<int, array<int, mixed>>
     */
    private static array $params = [];

    /**
     * @var array<string|int, mixed>
     */
    private static array $outputs = [];

    /**
     * @var array<string|int, StatusThread>
     */
    private static array $status = [];

    /**
     * @param string|int $name
     * @param callable $callback
     * @param array<int, mixed> $params
     */
    public static function register(string|int $name, callable $callback, array $params): void
    {
        if (isset(self::$outputs[$name]))
        {
            unset(self::$outputs[$name]);
        }

        self::$names[]  = $name;
        self::$fibers[] = new Fiber($callback);
        self::$params[] = $params;
        self::$status[$name] = new StatusThread();
    }

    /**
     * @throws Throwable
     */
    public static function run(): void
    {
        foreach (self::$fibers as $i => $fiber)
        {
            if (!self::$status[self::$names[$i]]->canWakeUp())
            {
                continue;
            }

            $name = self::$names[$i];

            try
            {
                if (!$fiber->isStarted())
                {
                    $fiber->start(...self::$params[$i]);
                }
                elseif ($fiber->isTerminated())
                {
                    self::$outputs[$name] = $fiber->getReturn();
                    unset(self::$fibers[$i]);
                }
                elseif ($fiber->isSuspended())
                {
                    $fiber->resume();
                }
            }
            catch (Throwable $e)
            {
                self::$outputs[$name] = $e;
            }
        }
    }

    public static function clear(): void
    {
        self::$names  = [];
        self::$fibers = [];
        self::$params = [];
    }

    /**
     * @return array<int, string|int>
     */
    public static function getNames(): array
    {
        return self::$names;
    }

    /**
     * @return array<int, Fiber>
     */
    public static function getFibers(): array
    {
        return self::$fibers;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public static function getParams(): array
    {
        return self::$params;
    }

    /**
     * @return array<string|int, mixed>
     */
    public static function getOutputs(): array
    {
        return self::$outputs;
    }

    /**
     * @param string|int $name
     * @return mixed
     */
    public static function getOutput(string|int $name): mixed
    {
        return self::$outputs[$name];
    }

    /**
     * @throws Throwable
     */
    public static function sleep(string $name, int $seconds): void
    {
        self::$status[$name]->sleep($seconds);

        $fiberCurrent = Fiber::getCurrent();

        if ($fiberCurrent !== null)
        {
            $fiberCurrent::suspend();
        }
    }

    public static function getStatus(string|int $name): StatusThread|null
    {
        return self::$status[$name] ?? null;
    }

}