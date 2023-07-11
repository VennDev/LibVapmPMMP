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

use Throwable;

interface SystemInterface
{

    /**
     * @throws Throwable
     *
     * This function is used to run the event loop with multiple event loops
     */
    public static function runEventLoop(): void;

    /**
     * @throws Throwable
     *
     * This function is used to run the event loop with single event loop
     */
    public static function runSingleEventLoop(): void;

    /**
     * This function is used to run a callback in the event loop with timeout
     */
    public static function setTimeout(callable $callback, int $timeout): SampleMacro;

    /**
     * This function is used to clear the timeout
     */
    public static function clearTimeout(SampleMacro $sampleMacro): void;

    /**
     * This function is used to run a callback in the event loop with interval
     */
    public static function setInterval(callable $callback, int $interval): SampleMacro;

    /**
     * This function is used to clear the interval
     */
    public static function clearInterval(SampleMacro $sampleMacro): void;

    /**
     * @param string $url
     * @param array<string|null, string|array> $options
     * @return Promise when Promise resolve InternetRequestResult and when Promise reject Error
     * @throws Throwable
     * @phpstan-param array{method?: string, headers?: array<int, string>, timeout?: int, body?: array<string, string>} $options
     */
    public static function fetch(string $url, array $options = []) : Promise;

    /**
     * @throws Throwable
     *
     * This is a function used only to retrieve results from an address or file path via the file_get_contents method
     */
    public static function read(string $path) : Promise;

}