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