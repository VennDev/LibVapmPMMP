<?php

namespace vennv;

use Throwable;

interface InterfaceAsync
{

    /**
     * This method is used to await a promise.
     */
    public static function await(callable|Promise|Async $callable) : mixed;

    /**
     * @throws Throwable
     *
     * This method is used to wait for all promises to be resolved.
     */
    public static function wait() : void;

    /**
     * This method is used to get the id of the promise.
     */
    public function getId() : int;

}