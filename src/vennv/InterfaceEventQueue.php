<?php

namespace vennv;

use Fiber;
use Throwable;

interface InterfaceEventQueue
{

    /**
     * @deprecated This method you should not use.
     */
    public static function getNextId() : int;

    /**
     * @deprecated This method you should not use.
     */
    public static function isMaxId() : bool;

    /**
     * @deprecated This method you should not use.
     */
    public static function addQueue(Fiber $fiber, bool $isPromise = false, bool $isPromiseAll = false, float $timeOut = 0.0) : int;

    /**
     * @deprecated This method you should not use.
     */
    public static function getQueue(int $id) : ?Queue;

    /**
     * @deprecated This method you should not use.
     */
    public static function getReturn(int $id) : mixed;

    /**
     * @deprecated This method you should not use.
     */
    public static function unsetReturn(int $id) : void;

    /**
     * @deprecated This method you should not use.
     */
    public static function runQueue(int $id) : void;

    /**
     * @deprecated This method you should not use.
     */
    public static function rejectQueue(int $id, mixed $result) : void;

    /**
     * @deprecated This method you should not use.
     */
    public static function fulfillQueue(int $id, mixed $result) : void;

}