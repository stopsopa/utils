<?php

namespace Stopsopa\Utils;

use PHPUnit_Framework_TestCase;

use Stopsopa\Utils\Utils\UtilString;

class TestAbstract extends PHPUnit_Framework_TestCase {

    protected static $cut;

    public static function testCut() {

        if (!static::$cut) {

            ob_start();

            var_dump('a-a');

            $data = ob_get_clean();

            $data = explode("\n", $data);

            static::$cut = isset($data[1]) && (strpos($data[1], 'a-a') !== false);
        }

        return static::$cut;
    }
    public static function dump($d, $back = 1) {

        if ($back < 1) {
            $back = 1;
        }

        $trace = debug_backtrace();

        while ($back) {

            $back -= 1;

            $t = array_shift($trace);
        }

        $line = $t['file'];

        if (strpos(strtolower($line), '.php') === (strlen($line) - 4) ) {
            $line = substr($line, 0, -4);
        }

        $line .= ':'.$t['line'];

        ob_start();

        var_dump($d);

        $dump = ob_get_clean();

        if (static::testCut()) {

            $dump = explode("\n", $dump);

            array_shift($dump);

            $dump = implode("\n", $dump);
        }

        return $line . "\n" . $dump;

    }
    public static function d($d, $back = 1) {

        fwrite(STDOUT, static::dump($d, $back + 1));
    }
}