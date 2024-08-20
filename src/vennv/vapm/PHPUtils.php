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

use Throwable;

final class PHPUtils
{

    /**
     * @throws Throwable
     * @param array<int|float|string|object> $array
     * @param callable $callback
     * @return Async
     *
     * @phpstan-param array<int|float|string|object> $array
     */
    public static function forEach(array $array, callable $callback): Async
    {
        return new Async(function () use ($array, $callback) {
            foreach ($array as $key => $value) {
                $callback($key, $value);
                FiberManager::wait();
            }
        });
    }

    /**
     * @throws Throwable
     * @param array<int|float|string|object> $array
     * @param callable $callback
     * @return Async
     *
     * @phpstan-param array<int|float|string|object> $array
     */
    public static function arrayMap(array $array, callable $callback): Async
    {
        return new Async(function () use ($array, $callback) {
            $result = [];
            foreach ($array as $key => $value) {
                $result[$key] = $callback($key, $value);
                FiberManager::wait();
            }
            return $result;
        });
    }

    /**
     * @throws Throwable
     * @param array<int|float|string|object> $array
     * @param callable $callback
     * @return Async
     *
     * @phpstan-param array<int|float|string|object> $array
     */
    public static function arrayFilter(array $array, callable $callback): Async
    {
        return new Async(function () use ($array, $callback) {
            $result = [];
            foreach ($array as $key => $value) {
                if ($callback($key, $value)) {
                    $result[$key] = $value;
                }
                FiberManager::wait();
            }
            return $result;
        });
    }

    /**
     * @param array<int|float|string|object> $array
     * @param callable $callback
     * @param mixed $initialValue
     * @return Async
     *
     * @throws Throwable
     */
    public static function arrayReduce(array $array, callable $callback, mixed $initialValue): Async
    {
        return new Async(function () use ($array, $callback, $initialValue) {
            $accumulator = $initialValue;
            foreach ($array as $key => $value) {
                $accumulator = $callback($accumulator, $value, $key);
                FiberManager::wait();
            }
            return $accumulator;
        });
    }

    /**
     * @param array<int|float|string|object> $array
     * @param string $className
     * @return Async
     *
     * @throws Throwable
     */
    public static function instanceOfAll(array $array, string $className): Async
    {
        return new Async(function () use ($array, $className) {
            foreach ($array as $value) {
                if (!($value instanceof $className)) return false;
                FiberManager::wait();
            }
            return true;
        });
    }

    /**
     * @param array<int|float|string|object> $array
     * @param string $className
     * @return Async
     *
     * @throws Throwable
     */
    public static function instanceOfAny(array $array, string $className): Async
    {
        return new Async(function () use ($array, $className) {
            foreach ($array as $value) {
                if ($value instanceof $className) return true;
                FiberManager::wait();
            }
            return false;
        });
    }

}
