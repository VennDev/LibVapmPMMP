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

use Generator;
use Throwable;

interface StreamInterface
{

    /**
     * @throws Throwable
     *
     * Use this to read a file or url.
     */
    public static function read(string $path) : Promise;

    /**
     * @throws Throwable
     *
     * Use this to write to a file or url.
     */
    public static function write(string $path, string $data) : Promise;

    /**
     * @throws Throwable
     *
     * Use this to append to a file or url.
     */
    public static function append(string $path, string $data) : Promise;

    /**
     * @throws Throwable
     *
     * Use this to delete a file or url.
     */
    public static function delete(string $path) : Promise;

    /**
     * @throws Throwable
     *
     * Use this to create a file.
     */
    public static function create(string $path) : Promise;

    /**
     * @throws Throwable
     *
     * Use this to create a file or overwrite a file.
     */
    public static function overWrite(string $path, string $data) : Promise;

}

final class Stream implements StreamInterface
{

    /**
     * @throws Throwable
     */
    public static function read(string $path): Promise
    {
        return new Promise(function($resolve , $reject) use ($path): void
        {
            $generator = function($path) use ($reject): Generator
            {
                $handle = fopen($path, 'r');

                if ($handle === false)
                {
                    $reject(Error::UNABLE_TO_OPEN_FILE);
                }
                else
                {
                    stream_set_blocking($handle, false);

                    while (($line = fgets($handle)) !== false)
                    {
                        yield $line;
                    }
                }
            };

            $lines = '';

            foreach ($generator($path) as $line)
            {
                $lines .= $line . PHP_EOL;
            }

            $resolve($lines);
        });
    }

    /**
     * @throws Throwable
     */
    public static function write(string $path, string $data): Promise
    {
        return new Promise(function($resolve , $reject) use ($path, $data): void
        {
            $generator = function($path, $data) use ($reject): Generator
            {
                $handle = fopen($path, 'w');

                if ($handle === false)
                {
                    $reject(Error::UNABLE_TO_OPEN_FILE);
                }
                else
                {
                    stream_set_blocking($handle, false);

                    yield fwrite($handle, $data);
                }
            };

            $generator($path, $data);

            $resolve();
        });
    }

    /**
     * @throws Throwable
     */
    public static function append(string $path, string $data): Promise
    {
        return new Promise(function($resolve , $reject) use ($path, $data): void
        {
            $generator = function($path, $data) use ($reject): Generator
            {
                $handle = fopen($path, 'a');

                if ($handle === false)
                {
                    $reject(Error::UNABLE_TO_OPEN_FILE);
                }
                else
                {
                    stream_set_blocking($handle, false);

                    yield fwrite($handle, $data);
                }
            };

            $generator($path, $data);

            $resolve();
        });
    }

    /**
     * @throws Throwable
     */
    public static function delete(string $path): Promise
    {
        return new Promise(function($resolve , $reject) use ($path): void
        {
            $generator = function($path) use ($reject): Generator
            {
                if (file_exists($path))
                {
                    yield unlink($path);
                }
                else
                {
                    $reject(Error::FILE_DOES_NOT_EXIST);
                }
            };

            $generator($path);

            $resolve();
        });
    }

    /**
     * @throws Throwable
     */
    public static function create(string $path): Promise
    {
        return new Promise(function($resolve , $reject) use ($path): void
        {
            $generator = function($path) use ($reject): Generator
            {
                if (!file_exists($path))
                {
                    yield touch($path);
                }
                else
                {
                    $reject(Error::FILE_ALREADY_EXISTS);
                }
            };

            $generator($path);

            $resolve();
        });
    }

    /**
     * @throws Throwable
     */
    public static function overWrite(string $path, string $data): Promise
    {
        return new Promise(function($resolve , $reject) use ($path, $data): void
        {
            $generator = function($path, $data) use ($reject): Generator
            {
                $handle = fopen($path, 'w+');

                if ($handle === false)
                {
                    $reject(Error::UNABLE_TO_OPEN_FILE);
                }
                else
                {
                    stream_set_blocking($handle, false);

                    yield fwrite($handle, $data);
                }
            };

            $generator($path, $data);

            $resolve();
        });
    }

}