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

interface CoroutineThreadInterface
{

    /**
     * @return void
     *
     * This function runs the callback function for the thread.
     */
    public function onRun(): void;

}

final class CoroutineThread extends Thread implements CoroutineThreadInterface
{

    private mixed $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
        parent::__construct($callback);
    }

    public function onRun(): void
    {
        if (is_callable($this->callback)) {
            $callback = call_user_func($this->callback);
            if (!is_string($callback)) $callback = serialize($callback);
            self::post($callback);
        }
    }

}