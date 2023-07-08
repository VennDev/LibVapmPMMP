<?php

namespace vennv;

final class Error 
{

    public const ALL_PROMISES_WERE_REJECTED = "All promises were rejected";

    public const QUEUE_IS_TIMEOUT = "Queue with id %id% timed out";

    public const QUEUE_NOT_FOUND = "Queue with id %id% not found";

    public const QUEUE_STILL_PENDING = "Queue with id %id% still pending";

    public const FAILED_TO_INITIALIZE_CURL = "Failed to initialize cURL";

}