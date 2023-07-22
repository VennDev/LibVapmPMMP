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

use TypeError;

final class AssumptionFailedError extends TypeError
{

    public function __construct(
        protected string $errorMessage,
        protected int $errorCode = 0
    )
    {
        parent::__construct(
            $this->errorMessage,
            $this->errorCode
        );
    }

    public function __toString() : string
    {
        return __CLASS__ . ": [$this->errorCode]: $this->errorMessage\n";
    }

}
