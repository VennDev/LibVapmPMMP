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

abstract class Thread implements ThreadInterface
{

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
                'vendor\autoload.php',
                $pathAutoLoad
            );

            $command = 'php -r "require_once \'' . $pathAutoLoad . '\'; include \'' . $class . '\'; $class = new ' . static::class . '(); $class->run();"';

            $process = proc_open(
                $command,
                $mode,
                $pipes
            );

            if (is_resource($process))
            {
                stream_set_blocking($pipes[1], false);
                stream_set_blocking($pipes[2], false);

                fclose($pipes[0]);

                while (proc_get_status($process)['running'])
                {
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