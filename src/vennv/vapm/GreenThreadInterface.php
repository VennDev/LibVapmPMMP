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

interface GreenThreadInterface
{

    /**
     * @param string|int $name
     * @param callable $callback
     * @param array<int, mixed> $params
     *
     * This method is used to register a green thread.
     */
    public static function register(string|int $name, callable $callback, array $params): void;

    /**
     * @throws Throwable
     */
    public static function run(): void;

    /**
     * This method is used to clear the data of the green threads.
     */
    public static function clear(): void;

    /**
     * @return array<int, string|int>
     *
     * This method is used to get the names of the green threads.
     */
    public static function getNames(): array;

    /**
     * @return array<int, Fiber>
     *
     * This method is used to get the fibers of the green threads.
     */
    public static function getFibers(): array;

    /**
     * @return array<int, array<int, mixed>>
     *
     * This method is used to get the params of the green threads.
     */
    public static function getParams(): array;

    /**
     * @return array<string|int, mixed>
     *
     * This method is used to get the outputs of the green threads.
     */
    public static function getOutputs(): array;

    /**
     * @param string|int $name
     * @return mixed
     *
     * This method is used to get the output of a green thread.
     */
    public static function getOutput(string|int $name): mixed;

    /**
     * @throws Throwable
     */
    public static function sleep(string $name, int $seconds): void;

    /**
     * @param string|int $name
     * @return StatusThread|null
     *
     * This method is used to get the status of a green thread.
     */
    public static function getStatus(string|int $name): StatusThread|null;

}