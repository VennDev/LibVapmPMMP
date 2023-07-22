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
use function microtime;

final class MicroTask
{

    /**
     * @var array<int, Promise>
     */
    private static array $tasks = [];

    public static function addTask(int $id, Promise $promise): void
    {
        self::$tasks[$id] = $promise;
    }

    public static function removeTask(int $id): void
    {
        unset(self::$tasks[$id]);
    }

    public static function getTask(int $id): ?Promise
    {
        return self::$tasks[$id] ?? null;
    }

    /**
     * @return array<int, Promise>
     */
    public static function getTasks(): array
    {
        return self::$tasks;
    }

    /**
     * @throws Throwable
     */
    public static function run(): void
    {
        foreach (self::$tasks as $id => $promise)
        {
            $promise->useCallbacks();
            $promise->setTimeEnd(microtime(true));
            
            EventLoop::addReturn($promise);

            self::removeTask($id);
        }
    }

}