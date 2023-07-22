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

use ReflectionClass;
use ReflectionException;
use Throwable;
use function is_string;
use function is_array;
use function explode;
use function fwrite;
use function fclose;
use function proc_open;
use function proc_get_status;
use function is_resource;
use function stream_get_contents;
use function stream_set_blocking;
use function json_decode;
use function json_encode;
use function str_replace;
use function get_called_class;

interface ThreadInterface
{

    /**
     * This abstract method use to run the thread
     */
    public function onRun(): void;

    /**
     * @param array<int, array<string>> $mode
     * @throws ReflectionException
     * @throws Throwable
     * @phpstan-param array<int, array<string>> $mode
     *
     * This method use to start the thread
     */
    public function start(array $mode = DescriptorSpec::BASIC): Async;

}

interface ThreadedInterface
{

    /**
     * @return string
     */
    public function getInput(): string;

    /**
     * This method use to get the pid of the thread
     */
    public function getPid(): int;

    /**
     * This method use to get the exit code of the thread
     */
    public function getExitCode(): int;

    /*
     * This method use to get the running status of the thread
     */
    public function isRunning(): bool;

    /**
     * This method use to get the signaled status of the thread
     */
    public function isSignaled(): bool;

    /**
     * This method use to get the stopped status of the thread
     */
    public function isStopped(): bool;

    /**
     * @return array<string, mixed>
     * @phpstan-return array<string, mixed>
     *
     * This method use to get the shared data of the main thread
     */
    public static function getDataMainThread(): array;

    /**
     * @param array<string, mixed> $shared
     * @phpstan-param array<string, mixed> $shared
     *
     * This method use to set the shared data of the main thread
     */
    public static function setShared(array $shared): void;

    /**
     * @param string $key
     * @param mixed $value
     * @phpstan-param mixed $value
     *
     * This method use to add the shared data of the MAIN-THREAD
     */
    public static function addShared(string $key, mixed $value): void;

    /**
     * @return array<string, mixed>
     *
     * This method use to get the shared data of the child thread
     */
    public static function getSharedData(): array;

    /**
     * @param array<string, mixed> $data
     * @return void
     * @phpstan-param array<string, mixed> $data
     *
     * This method use to post all data the main thread
     */
    public static function postMainThread(array $data): void;

    /**
     * @param string $data
     * @return void
     *
     * This method use to load the shared data from the main thread
     */
    public static function loadSharedData(string $data): void;

    /**
     * @param string $data
     * @return void
     *
     * This method use to alert for the main thread
     */
    public static function alert(string $data): void;

    /**
     * @param int $pid
     * @return bool
     *
     * This method use to check the thread is running or not
     */
    public static function threadIsRunning(int $pid): bool;

    /**
     * @param int $pid
     * @return bool
     *
     * This method use to kill the thread
     */
    public static function killThread(int $pid): bool;

}

abstract class Thread implements ThreadInterface, ThreadedInterface
{

    private const POST_MAIN_THREAD = 'postMainThread'; // example: postMainThread=>{data}

    private const POST_ALERT_THREAD = 'postAlertThread'; // example: postAlertThread=>{data}

    private int $pid = -1;

    private int $exitCode = -1;

    private bool $isRunning = false;

    private bool $signaled = false;

    private bool $stopped = false;

    /**
     * @var array<string, mixed>
     * @phpstan-var array<string, mixed>
     */
    private static array $shared = [];

    /**
     * @var array<int, Thread>
     * @phpstan-var array<int, Thread>
     */
    private static array $threads = [];

    /**
     * @var array<string, string>
     * @phpstan-var array<string, string>
     */
    private static array $inputs = [];

    public function __construct(string $input)
    {
        self::$inputs[get_called_class()] = $input;
    }

    public function getInput(): string
    {
        return self::$inputs[get_called_class()];
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    protected function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }

    public function isRunning(): bool
    {
        return $this->isRunning;
    }

    protected function setRunning(bool $isRunning): void
    {
        $this->isRunning = $isRunning;
    }

    public function isSignaled(): bool
    {
        return $this->signaled;
    }

    protected function setSignaled(bool $signaled): void
    {
        $this->signaled = $signaled;
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }

    protected function setStopped(bool $stopped): void
    {
        $this->stopped = $stopped;
    }

    /**
     * @return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    public static function getDataMainThread(): array
    {
        return self::$shared;
    }

    /**
     * @param array<string, mixed> $shared
     * @phpstan-param array<string, mixed> $shared
     */
    public static function setShared(array $shared): void
    {
        self::$shared = $shared;
    }

    public static function addShared(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function getSharedData(): array
    {
        $data = fgets(STDIN);

        if (is_string($data))
        {
            $data = json_decode($data, true);

            if (is_array($data))
            {
                return $data;
            }
        }

        return [];
    }

    /**
     * @param array<string, mixed> $data
     * @phpstan-param array<string, mixed> $data
     */
    public static function postMainThread(array $data): void
    {
        fwrite(STDOUT, self::POST_MAIN_THREAD . '=>' . json_encode($data) . PHP_EOL);
    }

    private static function isPostMainThread(string $data): bool
    {
        return explode('=>', $data)[0] === self::POST_MAIN_THREAD;
    }

    public static function loadSharedData(string $data): void
    {
        $data = explode('=>', $data);

        if ($data[0] === self::POST_MAIN_THREAD)
        {
            $result = json_decode($data[1], true);

            if (is_array($result))
            {
                self::setShared(array_merge(self::$shared, $result));
            }
        }
    }

    public static function alert(string $data): void
    {
        fwrite(STDOUT, self::POST_ALERT_THREAD . '=>' . $data . PHP_EOL);
    }

    public static function threadIsRunning(int $pid): bool
    {
        return isset(self::$threads[$pid]);
    }

    public static function killThread(int $pid): bool
    {
        if (isset(self::$threads[$pid]))
        {
            $thread = self::$threads[$pid];

            if ($thread->isRunning())
            {
                $thread->setStopped(true);
                return true;
            }
        }

        return false;
    }

    private static function isAlert(string $data): bool
    {
        return explode('=>', $data)[0] === self::POST_ALERT_THREAD;
    }

    private static function loadAlert(string $data): void
    {
        $data = explode('=>', $data);

        if ($data[0] === self::POST_ALERT_THREAD)
        {
            $result = json_decode($data[1], true);

            echo $result . PHP_EOL;
        }
    }

    /**
     * @param false|string $data
     * @return array<int, mixed>
     * @phpstan-return array<int, mixed>
     */
    private static function getAlert(false|string $data): array
    {
        $result = [];

        if (is_string($data))
        {
            $explode = explode(PHP_EOL, $data);

            foreach ($explode as $item)
            {
                if ($item !== '')
                {
                    $dataExplode = explode('=>', $data);

                    if ($dataExplode[0] === self::POST_ALERT_THREAD)
                    {
                        $result[] = json_decode($dataExplode[1], true);
                    }
                }
            }
        }

        return $result;
    }

    abstract public function onRun(): void;

    /**
     * @param array<int, array<string>> $mode
     * @return Async
     * @throws ReflectionException
     * @throws Throwable
     * @phpstan-param array<int, array<string>> $mode
     * @phpstan-return Async
     */
    public function start(array $mode = DescriptorSpec::BASIC): Async
    {
        return new Async(function() use ($mode): array
        {
            $className = get_called_class();

            $reflection = new ReflectionClass($className);

            $class = $reflection->getFileName();

            $pathAutoLoad = __FILE__;
            $pathAutoLoad = str_replace(
                'src\vennv\vapm\Thread.php',
                'src\vendor\autoload.php',
                $pathAutoLoad
            );

            $command = 'php -r "require_once \'' . $pathAutoLoad . '\'; include \'' . $class . '\'; $class = new ' . static::class . '(\'' . self::$inputs[get_called_class()] . '\'); $class->onRun();"';

            unset(self::$inputs[get_called_class()]);

            $process = proc_open(
                $command,
                $mode,
                $pipes
            );

            if (is_resource($process))
            {
                stream_set_blocking($pipes[1], false);
                stream_set_blocking($pipes[2], false);

                $data = json_encode(self::getDataMainThread());

                if (is_string($data))
                {
                    fwrite($pipes[0], $data);
                    fclose($pipes[0]);
                }

                while (proc_get_status($process)['running'])
                {
                    $status = proc_get_status($process);

                    if (!isset(self::$threads[$status['pid']]))
                    {
                        $this->setPid($status['pid']);

                        self::$threads[$status['pid']] = $this;
                    }

                    $thread = self::$threads[$status['pid']];

                    $thread->setExitCode($status['exitcode']);
                    $thread->setRunning($status['running']);
                    $thread->setSignaled($status['signaled']);
                    $thread->setStopped($status['stopped']);

                    if ($thread->isStopped())
                    {
                        proc_terminate($process);
                        break;
                    }

                    FiberManager::wait();
                }

                $output = stream_get_contents($pipes[1]);
                $error = stream_get_contents($pipes[2]);

                fclose($pipes[1]);
                fclose($pipes[2]);

                if ($error !== '' && is_string($error))
                {
                    throw new ThreadException($error);
                }
                else
                {
                    if (!is_bool($output))
                    {
                        $explode = explode(PHP_EOL, $output);

                        foreach ($explode as $item)
                        {
                            if ($item !== '')
                            {
                                if (self::isPostMainThread($item))
                                {
                                    self::loadSharedData($item);
                                }

                                if (self::isAlert($item))
                                {
                                    self::loadAlert($item);
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                throw new ThreadException(Error::UNABLE_START_THREAD);
            }

            proc_close($process);
            unset(self::$threads[$this->getPid()]);

            return self::getAlert($output);
        });
    }

}