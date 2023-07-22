<?php

/*
 * Copyright (c) 2023 VennV, CC0 1.0 Universal
 *
 * CREATIVE COMMONS CORPORATION IS NOT A LAW FIRM AND DOES NOT PROVIDE
 * LEGAL SERVICES. DISTRIBUTION OF THIS DOCUMENT DOES NOT CREATE AN
 * ATTORNEY-CLIENT RELATIONSHIP. CREATIVE COMMONS PROVIDES THIS
 * INFORMATION ON AN "AS-IS" BASIS. CREATIVE COMMONS MAKES NO WARRANTIES
 * REGARDING THE USE OF THIS DOCUMENT OR THE INFORMATION OR WORKS
 * PROVIDED HEREUNDER, AND DISCLAIMS LIABILITY FOR DAMAGES RESULTING FROM
 * THE USE OF THIS DOCUMENT OR THE INFORMATION OR WORKS PROVIDED
 * HEREUNDER.
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