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