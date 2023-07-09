<?php

namespace vennv;

use Throwable;

interface InterfacePromise
{

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved.
     */
    public function then(callable $callable) : ?Queue;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is rejected.
     */
    public function catch(callable $callable) : ?Queue;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved or rejected.
     */
    public static function resolve(int $id, mixed $result) : void;

    /**
     * This method is used to add a callback to the queue of callbacks
     * that will be executed when the promise is resolved or rejected.
     */
    public static function reject(int $id, mixed $result) : void;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Fulfills when all the promises fulfill, rejects when any of the promises rejects.
     */
    public static function all(array $promises) : Promise;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Settles when any of the promises settles.
     * In other words, fulfills when any of the promises fulfills, rejects when any of the promises rejects.
     */
    public static function race(array $promises) : Promise;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Fulfills when any of the promises fulfills, rejects when all the promises reject.
     */
    public static function any(array $promises) : Promise;

    /**
     * @throws Throwable
     * @param array<callable|Promise|Async> $promises
     *
     * Fulfills when all promises settle.
     */
    public static function allSettled(array $promises) : Promise;

    /**
     * @throws Throwable
     *
     * This method is used to get the id of the promise.
     */
    public function getId() : int;

}