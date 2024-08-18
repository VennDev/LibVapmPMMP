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

interface ClosureThreadInterface
{

    /**
     * @return void
     *
     * This function runs the callback function for the thread.
     */
    public function onRun(): void;

}

final class ClosureThread extends Thread implements ClosureThreadInterface
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
            if ($callback instanceof \Generator) {
                $callback = function () use ($callback): \Generator {
                    yield from $callback;
                };
                $callback = call_user_func($callback);
            }
            if (is_array($callback)) {
                $callback = json_encode($callback);
            } elseif (is_object($callback) && !$callback instanceof \Generator) {
                $callback = json_encode($callback);
            } elseif (is_bool($callback)) {
                $callback = $callback ? 'true' : 'false';
            } elseif (is_null($callback)) {
                $callback = 'null';
            } elseif ($callback instanceof \Generator) {
                $callback = json_encode(iterator_to_array($callback));
            } else {
                $callback = (string) $callback;
            }
            if (is_bool($callback)) $callback = (string) $callback;
            self::post($callback);
        }
    }

}