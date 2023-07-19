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

interface ThreadedInterface
{

    public function getPid(): int;

    public function setPid(int $pid): void;

    public function getExitCode(): int;

    public function setExitCode(int $exitCode): void;

    public function isRunning(): bool;

    public function setRunning(bool $isRunning): void;

    public function isSignaled(): bool;

    public function setSignaled(bool $signaled): void;

    public function isStopped(): bool;

    public function setStopped(bool $stopped): void;

    /**
     * @return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    public static function getShared(): array;

    /**
     * @param array<string, mixed> $shared
     * @phpstan-param array<string, mixed> $shared
     */
    public static function setShared(array $shared): void;

    public static function addShared(string $key, mixed $value): void;

}