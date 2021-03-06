<?php

namespace Stopsopa\UtilsBundle\Lib\Json;

use Exception;

/**
 * Wraper na natywne json_encode, json_decode.
 */
class Json
{
    public static function encode($data, $options = 0)
    {
        if ($data === false) {
            return 'false';
        }

        $tmp = json_encode($data, $options);

        if ($tmp === false) {
            throw new Exception('native 1: Nie powiodło się przekształcenie za pomocą json_encode'.print_r($data, true));
        }

        return $tmp;
    }
    public static function decode($json, $assoc = true)
    {
        return json_decode($json, $assoc);
    }
}
