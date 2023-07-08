<?php

namespace vennv;

use Fiber;

interface InterfaceEventQueue
{

    /**
     * This method to get the next id for the queue.
     */
    public static function getNextId() : int;

    /**
     * This method to check if the id is the maximum.
     */
    public static function isMaxId() : bool;

    /**
     * This method to add a queue.
     */
    public static function addQueue(
        Fiber $fiber,
        callable $promiseCallable,
        bool $isPromise = false,
        bool $isRepeatable = false,
        float $timeOut = 0.0
    ) : int;

    /**
     * This method to get a queue and check if it exists.
     */
    public static function getQueue(int $id) : ?Queue;

    /**
     * This method to get result of a queue and check if it exists.
     */
    public static function getReturn(int $id) : mixed;

    /**
     * This method to remove a result of a queue and check if it exists.
     */
    public static function unsetReturn(int $id) : void;

    /**
     * This method to run a queue with id.
     */
    public static function runQueue(int $id) : void;

    /**
     * This is method to reject a queue with result for id.
     */
    public static function rejectQueue(int $id, mixed $result) : void;

    /**
     * This is method to fulfill a queue with result for id.
     */
    public static function fulfillQueue(int $id, mixed $result) : void;

}