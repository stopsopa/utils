<?php

namespace Stopsopa\UtilsBundle\Lib;

class Currency {
    /**
     * Uwaga jak przyjdzie integer to nie dokonuje przekształceń w ogóle, zwraca jak jest
     *
     * Tutaj domyślnie obrąbuje do dwóch miejsc po przecinku, no ale w sumie przy walutach czy ma sens więcej?
     * Może w kantorach... do przemyślenia
     *
     * Koncepcja pracy tej biblioteki jest dosyć prosta,
     * Rezerwuje ona typ zmiennych integer jako reprezentację kwoty jako liczbę groszy
     * i użycie na integer metody toInt nie zmienia niczego
     * użycie jednak na int metody toDb normalizuje do zapisy string z kropką w środku
     * wrzucenie do toDb jakiegokolwiek stringu zawsze spowoduje próbę normalizacji i zwrócenia w jednej i tej samej formie
     * @param mixed $data
     * @return int
     * @throws Exception
     * test do przepisania do phpunit: DQp1c2UgTGliXEN1cnJlbmN5Ow0KDQoNCiRrID0gYXJyYXkoDQovLyAgICBhcnJheSgNCi8vICAgICAgICBuZXcgc3RkQ2xhc3MoKSwgMA0KLy8gICAgKSwNCiAgICBhcnJheSgNCiAgICAgICAgJzMxMjkuNTUnLCAzMTI5NTUNCiAgICApLA0KICAgIGFycmF5KA0KICAgICAgICAnMzEyOS41JywgMzEyOTUwDQogICAgKSwNCiAgICBhcnJheSgNCiAgICAgICAgJzAuNScsIDUwDQogICAgKSwNCiAgICBhcnJheSgNCiAgICAgICAgJy41JywgNTANCiAgICApLA0KICAgIGFycmF5KA0KICAgICAgICAnMzEyOScsIDMxMjkwMA0KICAgICksDQogICAgYXJyYXkoDQogICAgICAgICcwLjU1JywgNTUNCiAgICApLA0KICAgIGFycmF5KA0KICAgICAgICAxMDU0MywgMTA1NDMNCiAgICApLA0KICAgIGFycmF5KA0KICAgICAgICAnMScsIDEwMA0KICAgICksDQogICAgYXJyYXkoDQogICAgICAgIDEsIDENCiAgICApLA0KICAgIGFycmF5KA0KICAgICAgICAtMSwgLTENCiAgICApLA0KICAgIGFycmF5KA0KICAgICAgICAnLTEnLCAtMTAwDQogICAgKSwNCiAgICBhcnJheSgNCiAgICAgICAgJy0xMCcsIC0xMDAwDQogICAgKSwNCiAgICBhcnJheSgNCiAgICAgICAgJy0uMScsIC0xMA0KICAgICksDQogICAgYXJyYXkoDQogICAgICAgICctLjE3JywgLTE3DQogICAgKSwNCiAgICBhcnJheSgNCiAgICAgICAgJy01JywgLTUwMA0KICAgICksDQogICAgYXJyYXkoDQogICAgICAgICctNTAwJywgLTUwMDAwDQogICAgKSwNCg0KDQopOw0KDQpmb3JlYWNoICgkayBhcyAkdCkgew0KICAgICRzID0gJHRbMF07DQogICAgJHQgPSAkdFsxXTsNCg0KICAgIG5pZWdpbmllKGFycmF5KA0KICAgICAgICAnc291cmNlJyA9PiBkKCRzKSwNCiAgICAgICAgJ3RhcmdldCcgPT4gZCgkdCksDQogICAgICAgICd0b0ludCcgID0+IGQoQ3VycmVuY3k6OnRvSW50KCRzKSksDQogICAgICAgICd0b0ludC1lcScgPT4gZChDdXJyZW5jeTo6dG9JbnQoJHMpID09PSAkdCksDQogICAgICAgICcydG9JbnQnICA9PiBkKEN1cnJlbmN5Ojp0b0ludChDdXJyZW5jeTo6dG9JbnQoJHMpKSksDQogICAgICAgICcydG9JbnQtZXEnID0+IGQoQ3VycmVuY3k6OnRvSW50KEN1cnJlbmN5Ojp0b0ludCgkcykpID09PSAkdCksDQogICAgICAgICdhYmEnID0+IGQoQ3VycmVuY3k6OnRvSW50KEN1cnJlbmN5Ojp0b0RiKEN1cnJlbmN5Ojp0b0ludCgkcykpKSA9PT0gJHQpLA0KICAgICAgICAnYWJiYScgPT4gZChDdXJyZW5jeTo6dG9JbnQoQ3VycmVuY3k6OnRvRGIoQ3VycmVuY3k6OnRvRGIoQ3VycmVuY3k6OnRvSW50KCRzKSkpKSA9PT0gJHQpLA0KICAgICAgICAnYWJhYScgPT4gZChDdXJyZW5jeTo6dG9JbnQoQ3VycmVuY3k6OnRvRGIoQ3VycmVuY3k6OnRvSW50KEN1cnJlbmN5Ojp0b0ludCgkcykpKSkgPT09ICR0KSwNCiAgICAgICAgJ2FhYmEnID0+IGQoQ3VycmVuY3k6OnRvSW50KEN1cnJlbmN5Ojp0b0ludChDdXJyZW5jeTo6dG9EYihDdXJyZW5jeTo6dG9JbnQoJHMpKSkpID09PSAkdCkNCiAgICApLCAyKTsNCn0NCmRpZSgna29uaWVjJyk7DQoNCg0KLy8kayA9IGFycmF5KA0KLy8gICAgYXJyYXkoDQovLyAgICAgICAgJzMxMjk1NScsIC8vIHNvdXJjZQ0KLy8gICAgICAgICczMTI5NTUuMDAnIC8vIHRhcmdldA0KLy8gICAgKSwNCi8vICAgIGFycmF5KA0KLy8gICAgICAgICc0NS42NycsDQovLyAgICAgICAgJzQ1LjY3Jw0KLy8gICAgKSwNCi8vICAgIGFycmF5KA0KLy8gICAgICAgICc1NicsDQovLyAgICAgICAgJzU2LjAwJw0KLy8gICAgKSwNCi8vICAgIGFycmF5KA0KLy8gICAgICAgIDQ1LjY3LA0KLy8gICAgICAgICc0NS42NycNCi8vICAgICksDQovLyAgICBhcnJheSgNCi8vICAgICAgICAuNjcsDQovLyAgICAgICAgJzAuNjcnDQovLyAgICApLA0KLy8gICAgYXJyYXkoDQovLyAgICAgICAgLjY3OCwNCi8vICAgICAgICAnMC42NycNCi8vICAgICksDQovLyAgICBhcnJheSgNCi8vICAgICAgICAuMSwNCi8vICAgICAgICAnMC4xMCcNCi8vICAgICksDQovLyAgICBhcnJheSgNCi8vICAgICAgICAnLjEnLA0KLy8gICAgICAgICcwLjEwJw0KLy8gICAgKSwNCi8vICAgIGFycmF5KA0KLy8gICAgICAgIDEsDQovLyAgICAgICAgJzEuMDAnDQovLyAgICApLA0KLy8gICAgYXJyYXkoDQovLyAgICAgICAgMS4xLA0KLy8gICAgICAgICcxLjEwJw0KLy8gICAgKSwNCi8vICAgIGFycmF5KA0KLy8gICAgICAgIDEuMSwNCi8vICAgICAgICAnMS4xMCcNCi8vICAgICkNCi8vKTsNCi8vDQovLw0KLy9mb3JlYWNoICgkayBhcyAkZSkgew0KLy8gICAgbmllZ2luaWUoYXJyYXkoDQovLyAgICAgICAgJ3NvdXJjZScgPT4gZCgkZVswXSksDQovLyAgICAgICAgJ3RhcmdldCcgPT4gZCgkZVsxXSksDQovLyAgICAgICAgJ3RvRGInID0+IGQoQ3VycmVuY3k6OnRvRGIoJGVbMF0pKSwNCi8vICAgICAgICAnZXF1YWwnID0+IGQoQ3VycmVuY3k6OnRvRGIoJGVbMF0pID09PSAkZVsxXSksDQovLyAgICAgICAgJzJ0b0RiJyA9PiBkKEN1cnJlbmN5Ojp0b0RiKCRlWzBdKSksDQovLyAgICAgICAgJzJlcXVhbCcgPT4gZChDdXJyZW5jeTo6dG9EYihDdXJyZW5jeTo6dG9EYigkZVswXSkpID09PSAkZVsxXSkNCi8vICAgICksIDIpOw0KLy99DQovL2RpZSgna29uaWVjJyk7DQoNCg0KDQoNCg0KDQoNCg0KDQoNCg0KDQoNCg0KDQoNCg0KDQoNCg0KDQoNCg0KDQoNCmZ1bmN0aW9uIGQoJGQpIHsNCiAgICBvYl9zdGFydCgpOw0KICAgIHZhcl9kdW1wKCRkKTsNCiAgICByZXR1cm4gb2JfZ2V0X2NsZWFuKCk7DQp9
     */
    public static function toInt($data) {

        if (is_int($data)) {
            return $data;
        }

        if (!is_string($data) && !is_numeric($data)) {
            throw new Exception("Can't cast to int value '".gettype($data)."'");
        }

        $d = trim($data . '');

        $minus = false;
        if (strlen($d)  && $d[0] === '-') {
            $minus = true;
        }

        $d = trim($d, '-');

        $d = preg_split('#[^\d]#', $d);

        $c = count($d);

        switch ($c) {
            case 1;
                return intval(($minus ? '-' : '') . $d[0].'00');
            case 2;
                return intval(($minus ? '-' : '') . $d[0].str_pad(substr($d[1], 0, 2), 2, '0', STR_PAD_RIGHT));
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

        $minus = false;
        if (strlen($d) && $d[0] === '-') {
            $minus = true;
        }

        $d = trim($d, '-');

        $d = str_pad($d, 2, '0', STR_PAD_LEFT);

        $one = substr($d, 0, -2);

        $one = str_pad($one, 1, '0');

        $two = substr($d, -2);

        $two = str_pad($two, 2, '0', STR_PAD_RIGHT);

        return ($minus ? '-' : '') . "$one.$two";
    }
}