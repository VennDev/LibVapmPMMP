<?php

/**
 * Vapm and a brief idea of what it does.>
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

interface DeferredInterface
{

    /**
     * This method is used to get the result of the deferred.
     */
    public function await(): mixed;

}

final class Deferred implements DeferredInterface
{

    protected ChildCoroutine $childCoroutine;

    public function __construct(callable $callback)
    {
        $generator = call_user_func($callback);

        if ($generator instanceof Generator)
        {
            $this->childCoroutine = new ChildCoroutine(0, $generator);
        }
        else
        {
            throw new DeferredException(Error::DEFERRED_CALLBACK_MUST_RETURN_GENERATOR);
        }
    }

    public function await(): mixed
    {

        while (!$this->childCoroutine->isFinished())
        {
            $this->childCoroutine->run();
        }

        return $this->childCoroutine->getReturn();
    }

}