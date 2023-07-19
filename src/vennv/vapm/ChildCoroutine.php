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

use Generator;
use Exception;

interface ChildCoroutineInterface
{

    public function getId(): int;

    public function setException(Exception $exception): void;

    public function run(): void;

    public function isFinished(): bool;

    public function getReturn(): mixed;

}

final class ChildCoroutine implements ChildCoroutineInterface
{

    protected int $id;

    protected Generator $coroutine;

    protected Exception $exception;

    public function __construct(int $id, Generator $coroutine)
    {
        $this->id = $id;
        $this->coroutine = $coroutine;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setException(Exception $exception): void
    {
        $this->exception = $exception;
    }

    public function run(): void
    {
        $this->coroutine->send($this->coroutine->current());
    }

    public function isFinished(): bool
    {
        return !$this->coroutine->valid();
    }

    public function getReturn(): mixed
    {
        return $this->coroutine->getReturn();
    }

}