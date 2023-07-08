<?php

namespace vennv;

use Fiber;
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

    public static function fetch(string $url, array $options = [CURLOPT_RETURNTRANSFER => true]) : Promise 
    {
        return new Promise(function($resolve, $reject) use ($url, $options) 
        {
            $ch = curl_init($url);

            if ($ch === false)
            {
                $reject(Error::FAILED_TO_INITIALIZE_CURL);
            }
            else
            {
                curl_setopt_array($ch, $options);

                $result = curl_exec($ch);

                if (curl_errno($ch) !== 0)
                {
                    $reject(curl_error($ch));
                }
                else
                {
                    $resolve($result);
                }

                curl_close($ch);
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