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

use ReflectionClass;
use ReflectionException;
use Throwable;

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
    public function start(array $mode = DescriptorSpec::BASIC): void;

}

interface ThreadedInterface
{

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
     * This method use to get the shared data of the thread
     */
    public static function getShared(): array;

    /**
     * @param array<string, mixed> $shared
     * @phpstan-param array<string, mixed> $shared
     *
     * This method use to set the shared data of the thread
     */
    public static function setShared(array $shared): void;

    /**
     * @param string $key
     * @param mixed $value
     * @phpstan-param mixed $value
     *
     * This method use to add the shared data of the thread
     */
    public static function addShared(string $key, mixed $value): void;

    /**
     * @return false|string
     *
     * This method use to get the shared data of the thread
     */
    public static function getSharedData(): false|string;

}

abstract class Thread implements ThreadInterface, ThreadedInterface
{

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

    public function __construct()
    {}

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
    public static function getShared(): array
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

    public static function getSharedData(): false|string
    {
        return fgets(STDIN);
    }

    abstract public function onRun(): void;

    /**
     * @param array<int, array<string>> $mode
     * @throws ReflectionException
     * @throws Throwable
     * @phpstan-param array<int, array<string>> $mode
     */
    public function start(array $mode = DescriptorSpec::BASIC): void
    {
        new Async(function() use ($mode): void
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

            $command = 'php -r "require_once \'' . $pathAutoLoad . '\'; include \'' . $class . '\'; $class = new ' . static::class . '(); $class->onRun();"';

            $process = proc_open(
                $command,
                $mode,
                $pipes
            );

            if (is_resource($process))
            {
                stream_set_blocking($pipes[1], false);
                stream_set_blocking($pipes[2], false);

                $data = json_encode(self::getShared());

                if (is_string($data))
                {
                    fwrite($pipes[0], $data);
                    fclose($pipes[0]);
                }

                while (proc_get_status($process)['running'])
                {
                    $status = proc_get_status($process);

                    $this->setPid($status['pid']);
                    $this->setExitCode($status['exitcode']);
                    $this->setRunning($status['running']);
                    $this->setSignaled($status['signaled']);
                    $this->setStopped($status['stopped']);

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
                    echo $output;
                }
            }
            else
            {
                throw new ThreadException(Error::UNABLE_START_THREAD);
            }
        });
    }

}