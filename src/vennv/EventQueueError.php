<?php

namespace vennv;

use TypeError;

final class EventQueueError extends TypeError
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
