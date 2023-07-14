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

namespace vennv\vapm;

use Fiber;
use Throwable;

interface PromiseInterface
{

    /**
     * @throws Throwable
     *
     * This method is used to create a new promise.
     */
    public static function c(callable $callback, bool $justGetResult = false): Promise;

    /**
     * This method is used to get the id of the promise.
     */
    public function getId(): int;

    /**
     * This method is used to get the fiber of the promise.
     */
    public function getFiber(): Fiber;

    /**
     * This method is used to check if the promise is just to get the result.
     */
    public function isJustGetResult(): bool;

    /**
     * This method is used to get the time out of the promise.
     */
    public function getTimeOut(): float;

    /**
     * This method is used to get the time start of the promise.
     */
    public function getTimeStart(): float;

    /**
     * This method is used to get the time end of the promise.
     */
    public function getTimeEnd(): float;

    /**
     * This method is used to set the time out of the promise.
     */
    public function setTimeEnd(float $timeEnd): void;

    /**
     * This method is used to check if the promise is timed out and can be dropped.
     */
    public function canDrop(): bool;

    /**
     * This method is used to get the status of the promise.
     */
    public function getStatus(): string;

    /**
     * This method is used to check if the promise is pending.
     */
    public function isPending(): bool;

    /**
     * This method is used to check if the promise is resolved.
     */
    public function isResolved(): bool;

    /**
     * This method is used to check if the promise is rejected.
     */
    public function isRejected(): bool;

    /**
     * This method is used to get the result of the promise.
     */
    public function getResult(): mixed;

    /**
     * This method is used to get the return when catch or then of the promise is resolved or rejected.
     */
    public function getReturn(): mixed;

    /**
     * @throws Throwable
     *
     * This method is used to get the callback of the promise.
     */
    public function getCallback(): callable;

    /**
     * This method is used to resolve the promise.
     */
    public function resolve(mixed $value): void;

    /**
     * This method is used to reject the promise.
     */
    public function reject(mixed $value): void;

    /**
     * This method is used to set the callback when the promise is resolved.
     */
    public function then(callable $callback): Promise;

    /**
     * This method is used to set the callback when the promise is rejected.
     */
    public function catch(callable $callback): Promise;

    /**
     * This method is used to set the callback when the promise is resolved or rejected.
     */
    public function finally(callable $callback): Promise;

    /**
     * @throws Throwable
     *
     * This method is used to use the callbacks of the promise.
     */
    public function useCallbacks(): void;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function all(array $promises): Promise;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function allSettled(array $promises): Promise;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function any(array $promises): Promise;

    /**
     * @throws Throwable
     * @param array<int, Async|Promise|callable> $promises
     * @phpstan-param array<int, Async|Promise|callable> $promises
     */
    public static function race(array $promises): Promise;

}