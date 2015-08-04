<?php

namespace Stopsopa\UtilsBundle\Lib\Json\Conditionally;

use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Stopsopa\UtilsBundle\Lib\Json\Pretty\Json as PrettyJson;
use Exception;

/**
 * Warunkowy json.
 */
class Json
{
    public static function encode($data, $options = 0, $depth = 512)
    {
        if ($data === false) {
            return 'false';
        }

        if (AbstractApp::isDev()) {
            $tmp = PrettyJson::encode(
                $data,
                $options = PrettyJson::JSON_PRETTY_PRINT | PrettyJson::JSON_FORCEUTF8 | PrettyJson::JSON_UNESCAPED_SLASHES | PrettyJson::JSON_UNESCAPED_UNICODE,
                $depth
            );
        } else {
            $tmp = json_encode($data, $options, $depth);
        }

        if ($tmp === false) {
            throw new Exception('conditional: Nie powiodło się przekształcenie za pomocą json_encode'.print_r($data, true));
        }

        return $tmp;
    }

    public static function decode($json, $assoc = true, $depth = 512)
    {
        return json_decode($json, $assoc, $depth);
    }
}
