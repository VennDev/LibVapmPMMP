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

namespace vennv;

use Fiber;

interface InterfaceEventQueue
{

    /**
     * This method to get the next id for the queue.
     */
    public static function getNextId() : int;

    /**
     * This method to check if the id is the maximum.
     */
    public static function isMaxId() : bool;

    /**
     * This method to add a queue.
     */
    public static function addQueue(
        Fiber $fiber,
        callable $promiseCallable,
        bool $isPromise = false,
        bool $isRepeatable = false,
        float $timeOut = 0.0
    ) : int;

    /**
     * This method to get a queue and check if it exists.
     */
    public static function getQueue(int $id) : ?Queue;

    /**
     * This method to get result of a queue and check if it exists.
     */
    public static function getReturn(int $id) : mixed;

    /**
     * This method to remove a result of a queue and check if it exists.
     */
    public static function unsetReturn(int $id) : void;

    /**
     * This method to run a queue with id.
     */
    public static function runQueue(int $id) : void;

    /**
     * This is method to reject a queue with result for id.
     */
    public static function rejectQueue(int $id, mixed $result) : void;

    /**
     * This is method to fulfill a queue with result for id.
     */
    public static function fulfillQueue(int $id, mixed $result) : void;

}