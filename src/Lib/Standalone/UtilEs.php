<?php

namespace Stopsopa\UtilsBundle\Lib\Standalone;

use Exception;

class UtilEs
{
    public static function highlightLC($source, $target, $fromStart, $fromEnd, $toStart, $toEnd, $encoding = 'UTF8') {

        $fs = mb_strlen($fromStart, $encoding);
        $fe = mb_strlen($fromEnd, $encoding);
        $ts = mb_strlen($toStart, $encoding);
        $te = mb_strlen($toEnd, $encoding);

        if (!$fs || !$fe || !$ts || !$te) {
            throw new Exception("One of the parameters is empty: " . json_encode(array($fromStart, $fromEnd, $toStart, $toEnd), JSON_PRETTY_PRINT));
        }

        if ($fromStart === $toStart) {
            throw new Exception("From and To can't be the same: " . json_encode(array($fromStart, $fromEnd, $toStart, $toEnd), JSON_PRETTY_PRINT));
        }

        $slen = mb_strlen($source, $encoding);

        $tlen = mb_strlen($target, $encoding);

        if ($tlen >= $slen) {

            throw new Exception("Target length string ($tlen) shouldn't be equal/longer then source string length ($slen) in strings: " . static::formatSourceEnd($source, $target, $encoding));
        }

        $starts = array();
        $offset = 0;
        for (;;$offset <= $slen) {

            $found = mb_strpos($source, $fromStart, $offset, $encoding);

            if ($found > -1) {
                $starts[] = $found;

                $offset = $found + 1;
                continue;
            }

            if ($found === false) {
                break;
            }
        }

        $ends = array();
        $offset = 0;
        for (;;$offset <= $slen) {

            $found = mb_strpos($source, $fromEnd, $offset, $encoding);

            if ($found > -1) {
                $ends[] = $found;
                $offset = $found + 1;
                continue;
            }

            if ($found !== false) {
                continue;
            }

            $s = count($starts);

            $t = count($ends);

            if ( $s !== $t ) {

                throw new Exception("Not found the same number of starts ($s) and ends ($t) in strings: " . static::formatSourceEnd($source, $target, $encoding));
            }

            break;
        }

        $sd = $ts - $fs;
        $ed = $te - $fe;

        foreach ($starts as $index => $s) {

            $s = $starts[$index] + ($index * $sd) + ($index * $ed);

            $target = mb_substr($target, 0, $s, $encoding)
                . $toStart
                . mb_substr($target, $s, null, $encoding);

            $s = $ends[$index] + (($index + 1) * $sd) + ($index * $ed);

            $target = mb_substr($target, 0, $s,   $encoding)
                . $toEnd
                . mb_substr($target, $s, null, $encoding);
        }

        return $target;
    }
    protected static function formatSourceEnd($source, $target, $encoding) {

        $s = UtilString::subEndStartStop($source, $start = 0, $length = 100, $addtoendifcut = ' ...', $addtobeginifcut = '... ', $encoding);

        $t = UtilString::subEndStartStop($target, $start = 0, $length = 100, $addtoendifcut = ' ...', $addtobeginifcut = '... ', $encoding);

        return "\n\nSource:\n\n '$s'\n\nTarget:\n\n '$t'";
    }
}