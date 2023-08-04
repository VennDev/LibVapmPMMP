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

interface CoroutineThreadInterface {

    /**
     * @param callable $callback
     * @return void
     *
     * This function sets the callback function for the thread.
     */
    public function setCallback(callable $callback) : void;

    /**
     * @return void
     *
     * This function runs the callback function for the thread.
     */
    public function onRun() : void;

}

final class CoroutineThread extends Thread implements CoroutineThreadInterface {

    private mixed $callback = null;

    public function setCallback(callable $callback) : void {
        $this->callback = $callback;
    }

    public function onRun() : void {
        if (is_callable($this->callback)) {
            call_user_func($this->callback);
        }
    }

}