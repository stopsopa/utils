<?php

namespace Stopsopa\UtilsBundle\Lib;

class Random {
    /**
     * http://php.net/manual/en/function.openssl-random-pseudo-bytes.php#104322
     * @param $min
     * @param $max
     * @return mixed
     */
    public static function random($min, $max) {
        $max += 1;
        $range = $max - $min;
        if ($range == 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes, $s)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

}