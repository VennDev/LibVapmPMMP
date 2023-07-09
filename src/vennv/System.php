<?php

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
            if ($method === "GET") {
                $result = Internet::getURL($url, $options["timeout"] ?? 10, $options["headers"] ?? []);
            } else {
                $result = Internet::postURL($url, $options["body"] ?? [], $options["timeout"] ?? 10, $options["headers"] ?? []);
            }
            if ($result === null) {
                $reject("Error in fetching data!");
            } else {
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