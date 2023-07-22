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
            throw new DeferredException("Deferred callback must return a Generator");
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