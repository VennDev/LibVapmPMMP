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

use Fiber;
use Throwable;
use function is_null;

final class FiberManager
{

    /**
     * @throws Throwable
     */
    public static function wait(): void
    {
        $fiber = Fiber::getCurrent();

        if (!is_null($fiber))
        {
            Fiber::suspend();
        }
    }

}