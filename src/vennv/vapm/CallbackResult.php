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

interface CallbackResultInterface {

    /**
     * @return bool
     *
     * Returns true if the callback has an error.
     */
    public function hasError() : bool;

    /**
     * @return mixed
     *
     * Returns the result of the callback.
     */
    public function getResult() : mixed;

}

final class CallbackResult implements CallbackResultInterface {

    private bool $hasError;

    private mixed $result;

    public function __construct(bool $hasError, mixed $result) {
        $this->hasError = $hasError;
        $this->result = $result;
    }

    public function hasError() : bool {
        return $this->hasError;
    }

    public function getResult() : mixed {
        return $this->result;
    }

}