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
}