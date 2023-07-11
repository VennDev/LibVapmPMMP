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

final class SampleMacro implements SampleMacroInterface
{

    private float $timeOut;

    private float $timeStart;

    private bool $isRepeat;

    /** @var callable $callback */
    private mixed $callback;

    private int $id;

    public function __construct(callable $callback, float $timeOut = 0.0, bool $isRepeat = false)
    {
        $this->id = MacroTask::generateId();
        $this->timeOut = Utils::milliSecsToSecs($timeOut);
        $this->isRepeat = $isRepeat;
        $this->timeStart = microtime(true);
        $this->callback = $callback;
    }

    public function isRepeat(): bool
    {
        return $this->isRepeat;
    }

    public function getTimeOut(): float
    {
        return $this->timeOut;
    }

    public function getTimeStart(): float
    {
        return $this->timeStart;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function checkTimeOut(): bool
    {
        return microtime(true) - $this->timeStart >= $this->timeOut;
    }

    public function isRunning(): bool
    {
        return MacroTask::getTask($this->id) !== null;
    }

    public function run(): void
    {
        call_user_func($this->callback);
    }

    public function stop(): void
    {
        MacroTask::removeTask($this);
    }

}