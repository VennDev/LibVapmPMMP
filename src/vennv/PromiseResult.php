<?php

namespace vennv;

final class PromiseResult
{

    public function __construct(
        private readonly mixed $result,
        private readonly StatusQueue $status
    )
    {}

    public function getResult() : mixed
    {
        return $this->result;
    }

    public function getStatus() : StatusQueue
    {
        return $this->status;
    }

}