<?php

namespace Stopsopa\UtilsBundle\Lib;

use Stopsopa\UtilsBundle\Lib\Standalone\Urlizer;

class Debug {
    static $test = 0;
    public static function get() {
        return static::$test++;
    }
    public static function header($name) {
        $name = Urlizer::urlizeTrim($name);
        header('X-'.$name.': '.static::get());
    }
}