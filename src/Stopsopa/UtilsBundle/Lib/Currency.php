<?php

namespace Stopsopa\UtilsBundle\Lib;

class Currency {
    /**
     * Uwaga jak przyjdzie integer to nie dokonuje przekształceń w ogóle, zwraca jak jest
     *
     * Tutaj domyślnie obrąbuje do dwóch miejsc po przecinku, no ale w sumie przy walutach czy ma sens więcej?
     * Może w kantorach... do przemyślenia
     *
     * @param mixed $data
     * @return int
     * @throws Exception
     */
    public static function toInt($data) {

        if (is_int($data)) {
            return $data;
        }

        $d = $data . '';

        $d = preg_split('#[^\d]#', $d);

        $c = count($d);

        switch ($c) {
            case 1;
                return intval($d[0].'00');
            case 2;
                return intval($d[0].str_pad(substr($d[1], 0, 2), 2, '0', STR_PAD_RIGHT));
            default:
                throw new Exception("Can't cast to int value '".print_r($data, true)."'");
        }
    }

    /**
     * W przyszłości warto się zastanowić czy nie zrobić tutaj zaokrąglania lepszego,
     * a nie na zasadzie odrąbywania
     *
     * @param mixed $data
     * @return string
     * @throws Exception
     */
    public static function toDb($data) {

        $d = static::toInt($data);

        $d .= '';

        $one = substr($d, 0, -2);

        $one = str_pad($one, 1, '0');

        $two = substr($d, -2);

        $two = str_pad($two, 2, '0', STR_PAD_RIGHT);

        return "$one.$two";
    }
}