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

use vennv\vapm\utils\Utils;
use Throwable;
use RuntimeException;
use function is_callable;

interface AsyncInterface
{

    public function getId(): int;

    /**
     * @throws Throwable
     */
    public static function await(mixed $await): mixed;

}

final class Async implements AsyncInterface
{

    private Promise $promise;

    /**
     * @throws Throwable
     */
    public function __construct(callable $callback)
    {
        $promise = new Promise($callback, true);
        $this->promise = $promise;
    }

    public function getId(): int
    {
        return $this->promise->getId();
    }

    /**
     * @throws Throwable
     */
    public static function await(mixed $await): mixed
    {
        if (!Utils::isClass(Async::class)) throw new RuntimeException(Error::ASYNC_AWAIT_MUST_CALL_IN_ASYNC_FUNCTION);

        $result = $await;

        if (is_callable($await)) $await = new Async($await);

        if ($await instanceof Promise || $await instanceof Async) {
            $return = EventLoop::getReturn($await->getId());

            while ($return === null) {
                $return = EventLoop::getReturn($await->getId());
                FiberManager::wait();
            }

            if ($return instanceof Promise) $result = $return->getResult();
        }

        return $result;
    }

}