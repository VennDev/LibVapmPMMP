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

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

interface VapmPMMPInterface
{

    public static function init(PluginBase $plugin) : void;

}

final class VapmPMMP implements VapmPMMPInterface
{

    private static bool $isInit = false;

    public static function init(PluginBase $plugin) : void
    {
        if (!self::$isInit)
        {
            self::$isInit = true;

            $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(
                function() : void
                {
                    System::runEventLoop();
                }
            ), 1);
        }
    }

}
