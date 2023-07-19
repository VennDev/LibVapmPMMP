<?php

/*
 * Copyright (c) 2023 VennV
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types = 1);

namespace vennv\vapm;

use Throwable;
use function is_callable;

interface AsyncInterface
{

    public function getId(): int;

    /**
     * @throws Throwable
     */
    public static function await(Promise|Async|callable $await): mixed;

}

final class Async implements AsyncInterface
{

    private int $id;

    /**
     * @throws Throwable
     */
    public function __construct(callable $callback)
    {
        $promise = new Promise($callback, true);
        $this->id = $promise->getId();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @throws Throwable
     */
    public static function await(Promise|Async|callable $await): mixed
    {
        $result = null;

        if (is_callable($await))
        {
            $await = new Async($await);
        }

        $return = EventLoop::getReturn($await->getId());

        while ($return === null)
        {
            $return = EventLoop::getReturn($await->getId());
            FiberManager::wait();
        }

        if ($return instanceof Promise)
        {
            $result = $return->getResult();
        }

        return $result;
    }

}