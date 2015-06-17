<?php

namespace Stopsopa\UtilsBundle\Form;

class Test {
    static $test = 0;
    public static function get() {
        return static::$test++;
    }
}