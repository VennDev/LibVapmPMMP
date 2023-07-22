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

interface PromiseResultInterface
{

    public function getStatus(): string;

    public function getResult(): mixed;

}

final class PromiseResult implements PromiseResultInterface
{

    private string $status;

    private mixed $result;

    public function __construct(string $status, mixed $result)
    {
        $this->status = $status;
        $this->result = $result;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

}