<?php

namespace vennv;

use Error;

final class AggregateError extends Error
{

    public function __construct(
        protected $message,
        protected $code = 0
    )
    {
        parent::__construct(
            $message,
            $code
        );
    }

    public function __toString() : string
    {
        return __CLASS__ . ": [$this->code]: $this->message\n";
    }

}