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