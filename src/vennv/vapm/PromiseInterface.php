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

    public function getId(): int;

    public function getFiber(): Fiber;

    public function isJustGetResult(): bool;

    public function getTimeOut(): float;

    public function getTimeStart(): float;

    public function getTimeEnd(): float;

    public function setTimeEnd(float $timeEnd): void;

    public function canDrop(): bool;

    public function getStatus(): string;

    public function isPending(): bool;

    public function isResolved(): bool;

    public function isRejected(): bool;

    public function getResult(): mixed;

    public function getReturn(): mixed;

    public function getCallback(): callable;

    public function resolve(mixed $value): void;

    public function reject(mixed $value): void;

    public function then(callable $callback): Promise;

    public function catch(callable $callback): Promise;

    public function finally(callable $callback): Promise;

    /**
     * @throws Throwable
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