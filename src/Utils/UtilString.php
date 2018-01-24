<?php

namespace Stopsopa\Utils\Utils;

use Exception;

use Stopsopa\Utils\TestAbstract as A;

class UtilString
{
    public static function stripTags($string) {

        $string = preg_replace('/(<[^>]+?>)/m', ' ', $string);

        $string = preg_replace('/[\t\r\n\s\xC2\xA0]{1,}/mu', ' ', $string);

        return trim($string);
    }
    public static function toUnicode($str) {
        return preg_replace_callback("/./", function($matched) {
            return '\x'.dechex(ord($matched[0]));
        }, $str);
    }
}
