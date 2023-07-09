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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types = 1);

namespace vennv;

use Fiber;
use pocketmine\utils\Internet;
use Throwable;

final class System extends EventQueue implements InterfaceSystem
{

    /**
     * @throws Throwable
     */
    public static function setTimeout(callable $callable, int $timeout) : void
    {
        parent::addQueue(
            new Fiber($callable), 
            $callable,
            false, 
            false, 
            Utils::milliSecsToSecs($timeout)
        );
    }

    /**
     * @throws Throwable
     */
    public static function setInterval(callable $callable, int $interval): void
    {
        parent::addQueue(
            new Fiber($callable), 
            $callable,
            false, 
            true, 
            Utils::milliSecsToSecs($interval)
        );
    }

    public static function fetch(string $url, array $options = []) : Promise
    {
        return new Promise(function($resolve, $reject) use ($url, $options) 
        {
            $method = $options["method"] ?? "GET";
            if ($method === "GET") 
            {
                $result = Internet::getURL($url, $options["timeout"] ?? 10, $options["headers"] ?? []);
            } else 
            {
                $result = Internet::postURL($url, $options["body"] ?? [], $options["timeout"] ?? 10, $options["headers"] ?? []);
            }
            if ($result === null) 
            {
                $reject("Error in fetching data!");
            } 
            else 
            {
                $resolve($result);
            }
        });
    }
    
    public static function fetchJg(string $url) : Promise 
    {
        return new Promise(function($resolve, $reject) use ($url) 
        {
            $ch = file_get_contents($url);
            if ($ch === false)
            {
                $reject("Error in fetching data!");
            }
            else
            {
                $resolve($ch);
            }
        });
    }

    /**
     * @throws Throwable
     */
    public static function endSingleJob() : void
    {
        parent::runSingleJob();
    }

    /**
     * @throws Throwable
     */
    public static function endMultiJobs() : void
    {
        parent::runMultiJobs();
    }

}
