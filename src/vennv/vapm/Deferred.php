<?php

/**
 * Vapm - A library for PHP about Async, Promise, Coroutine, GreenThread,
 *      Thread and other non-blocking methods. The method is based on Fibers &
 *      Generator & Processes, requires you to have php version from >= 8.1
 *
 * Copyright (C) 2023  VennDev
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

declare(strict_types = 1);

namespace vennv\vapm;

use Generator;

interface DeferredInterface {

    /**
     * This method is used to get the result of the deferred.
     */
    public function await() : Generator;

    /**
     * @param DeferredInterface ...$deferreds
     * @return Generator
     *
     * This method is used to get the result of the deferred.
     */
    public static function awaitAll(DeferredInterface ...$deferreds) : Generator;

    /**
     * This method is used to get the child coroutine of the deferred.
     */
    public function getChildCoroutine() : ChildCoroutine;

    /**
     * This method is used to get the result of the deferred.
     */
    public function getComplete() : mixed;

}

final class Deferred implements DeferredInterface {

    protected mixed $return = null;

    protected ChildCoroutine $childCoroutine;

    public function __construct(callable $callback) {
        $generator = call_user_func($callback);

        if ($generator instanceof Generator) {
            $this->childCoroutine = new ChildCoroutine($generator);
        } else {
            throw new DeferredException(Error::DEFERRED_CALLBACK_MUST_RETURN_GENERATOR);
        }
    }

    public function await() : Generator {
        while (!$this->childCoroutine->isFinished()) {
            $this->childCoroutine->run();
            yield;
        }

        $this->return = $this->childCoroutine->getReturn();

        return $this->return;
    }

    public static function awaitAll(DeferredInterface ...$deferreds) : Generator {
        $result = [];

        while (count($result) <= count($deferreds)) {
            foreach ($deferreds as $index => $deferred) {
                $childCoroutine = $deferred->getChildCoroutine();

                if ($childCoroutine->isFinished()) {
                    $result[] = $childCoroutine->getReturn();
                    unset($deferreds[$index]);
                } else {
                    $childCoroutine->run();
                }
            }

            yield;
        }

        return $result;
    }

    public function getChildCoroutine() : ChildCoroutine {
        return $this->childCoroutine;
    }

    public function getComplete() : mixed {
        return $this->return;
    }

}