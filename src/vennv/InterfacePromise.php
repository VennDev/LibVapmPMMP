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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types = 1);

namespace vennv;

use Throwable;

interface InterfacePromise
{

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved.
     */
    public function then(callable $callable) : ?Queue;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is rejected.
     */
    public function catch(callable $callable) : ?Queue;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved or rejected.
     */
    public static function resolve(int $id, mixed $result) : void;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved or rejected.
     */
    public static function reject(int $id, mixed $result) : void;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Fulfills when all the promises fulfill, rejects when any of the promises rejects.
     */
    public static function all(array $promises) : Promise;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Settles when any of the promises settles.
     * In other words, fulfills when any of the promises fulfills, rejects when any of the promises rejects.
     */
    public static function race(array $promises) : Promise;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Fulfills when any of the promises fulfills, rejects when all the promises reject.
     */
    public static function any(array $promises) : Promise;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Fulfills when all promises settle.
     */
    public static function allSettled(array $promises) : Promise;

    /**
     * @throws Throwable
     *
     * This method is used to get the id of the promise.
     */
    public function getId() : int;

}