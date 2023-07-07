<?php

namespace vennv;

use Error;

final class AggregateError extends Error
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