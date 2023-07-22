<?php

/*
 * Copyright (c) 2023 VennV, CC0 1.0 Universal
 *
 * CREATIVE COMMONS CORPORATION IS NOT A LAW FIRM AND DOES NOT PROVIDE
 * LEGAL SERVICES. DISTRIBUTION OF THIS DOCUMENT DOES NOT CREATE AN
 * ATTORNEY-CLIENT RELATIONSHIP. CREATIVE COMMONS PROVIDES THIS
 * INFORMATION ON AN "AS-IS" BASIS. CREATIVE COMMONS MAKES NO WARRANTIES
 * REGARDING THE USE OF THIS DOCUMENT OR THE INFORMATION OR WORKS
 * PROVIDED HEREUNDER, AND DISCLAIMS LIABILITY FOR DAMAGES RESULTING FROM
 * THE USE OF THIS DOCUMENT OR THE INFORMATION OR WORKS PROVIDED
 * HEREUNDER.
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