<?php

namespace Stopsopa\UtilsBundle\Lib;

/**
 * Zwykłe funkcje json_encode, json_decode
 */
class Json
{
    public static function encode($data, $options = 0, $depth = 512)
    {
        return json_encode($data, $options, $depth);
    }
    public static function decode($json, $assoc = true, $depth = 512)
    {
        return json_decode($json, $assoc, $depth);
    }
}
