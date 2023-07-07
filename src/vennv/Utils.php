<?php

namespace vennv;

final class Utils {

    public static function milliSecsToSecs(float $milliSecs) : float
    {
        return $milliSecs / 1000;
    }

}