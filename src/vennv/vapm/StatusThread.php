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

use function microtime;

interface StatusThreadInterface
{

    /**
     * @return int|float
     *
     * This method is used to get the time sleeping.
     */
    public function getTimeSleeping(): int|float;

    /**
     * @return int|float
     *
     * This method is used to get the sleep start time.
     */
    public function getSleepStartTime(): int|float;

    /**
     * @param int|float $seconds
     *
     * This method is used to sleep the thread.
     */
    public function sleep(int|float $seconds): void;

    /**
     * @return bool
     *
     * This method is used to check if the thread can wake up.
     */
    public function canWakeUp(): bool;

}

final class StatusThread implements StatusThreadInterface
{

    private int|float $timeSleeping = 0;

    private int|float $sleepStartTime;

    public function __construct()
    {
        $this->sleepStartTime = microtime(true);
    }

    public function getTimeSleeping(): int|float
    {
        return $this->timeSleeping;
    }

    public function getSleepStartTime(): int|float
    {
        return $this->sleepStartTime;
    }

    public function sleep(int|float $seconds): void
    {
        $this->timeSleeping += $seconds;
    }

    public function canWakeUp(): bool
    {
        return microtime(true) - $this->sleepStartTime >= $this->timeSleeping;
    }

}