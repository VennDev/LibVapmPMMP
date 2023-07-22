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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use function preg_match;

final class Utils
{

    public static function milliSecsToSecs(float $milliSecs): float
    {
        return $milliSecs / 1000;
    }

    public static function getAllPHP(string $path): Generator
    {
        $dir = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($dir);

        foreach ($iterator as $file)
        {
            if ($file instanceof SplFileInfo)
            {
                $fname = $file->getFilename();

                if (preg_match('%\.php$%', $fname) === 1)
                {
                    yield $file->getPathname();
                }
            }
        }
    }

}