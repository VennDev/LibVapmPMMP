<?php

/**
 * Vapm and a brief idea of what it does.>
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