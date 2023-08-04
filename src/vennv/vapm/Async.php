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

use Throwable;
use function is_callable;

interface AsyncInterface {

    public function getId() : int;

    /**
     * @throws Throwable
     */
    public static function await(mixed $await) : mixed;

}

final class Async implements AsyncInterface {

    private int $id;

    /**
     * @throws Throwable
     */
    public function __construct(callable $callback) {
        $promise = new Promise($callback, true);
        $this->id = $promise->getId();
    }

    public function getId() : int {
        return $this->id;
    }

    /**
     * @throws Throwable
     */
    public static function await(mixed $await) : mixed {
        $result = $await;

        if (is_callable($await)) {
            $await = new Async($await);
        }

        if ($await instanceof Promise || $await instanceof Async) {
            $return = EventLoop::getReturn($await->getId());

            while ($return === null) {
                $return = EventLoop::getReturn($await->getId());
                FiberManager::wait();
            }

            if ($return instanceof Promise) {
                $result = $return->getResult();
            }
        }

        return $result;
    }

}