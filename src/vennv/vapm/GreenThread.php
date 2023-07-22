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

use Fiber;
use Throwable;

interface GreenThreadInterface
{

    /**
     * @param string|int $name
     * @param callable $callback
     * @param array<int, mixed> $params
     *
     * This method is used to register a green thread.
     */
    public static function register(string|int $name, callable $callback, array $params): void;

    /**
     * @throws Throwable
     */
    public static function run(): void;

    /**
     * This method is used to clear the data of the green threads.
     */
    public static function clear(): void;

    /**
     * @return array<int, string|int>
     *
     * This method is used to get the names of the green threads.
     */
    public static function getNames(): array;

    /**
     * @return array<int, Fiber>
     *
     * This method is used to get the fibers of the green threads.
     */
    public static function getFibers(): array;

    /**
     * @return array<int, array<int, mixed>>
     *
     * This method is used to get the params of the green threads.
     */
    public static function getParams(): array;

    /**
     * @return array<string|int, mixed>
     *
     * This method is used to get the outputs of the green threads.
     */
    public static function getOutputs(): array;

    /**
     * @param string|int $name
     * @return mixed
     *
     * This method is used to get the output of a green thread.
     */
    public static function getOutput(string|int $name): mixed;

    /**
     * @throws Throwable
     */
    public static function sleep(string $name, int $seconds): void;

    /**
     * @param string|int $name
     * @return StatusThread|null
     *
     * This method is used to get the status of a green thread.
     */
    public static function getStatus(string|int $name): StatusThread|null;

}

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