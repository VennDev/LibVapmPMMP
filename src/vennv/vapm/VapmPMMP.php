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

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

interface VapmPMMPInterface {

    public static function init(PluginBase $plugin) : void;

}

final class VapmPMMP implements VapmPMMPInterface {

    private static bool $isInit = false;

    public static function init(PluginBase $plugin) : void {
        if (!self::$isInit) {
            self::$isInit = true;

            $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(
                function () : void {
                    System::runEventLoop();
                }
            ), 1);
        }
    }

}