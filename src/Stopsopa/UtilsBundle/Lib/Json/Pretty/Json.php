<?php

namespace Stopsopa\UtilsBundle\Lib\Json\Pretty;
use Exception;

/**
 * Oparte na logice: https://github.com/ss23/phing2composer/blob/master/JsonFile.php
 * Wyszperane po rozebraniu composera z pliku phar
 * This is the JSON encoder lifted from Composer.
 * It will minimise the unnecessary differences made to composer.json files
 * Zawsze wyrzuca dane w formie czytelnej dla człowieka (z wzięciami)
 */
class Json
{
    const JSON_UNESCAPED_SLASHES    = 64;
    const JSON_PRETTY_PRINT         = 128;
    const JSON_UNESCAPED_UNICODE    = 256;
    const JSON_FORCEUTF8            = 512;
    const JSON_DEFAULT              = 960;

    /**
     * Encodes an array into (optionally pretty-printed) JSON
     * This code is based on the function found at:
     *  http://recursive-design.com/blog/2008/03/11/format-json-with-php/
     * Originally licensed under MIT by Dave Perrett <mail@recursive-design.com>
     *
     * @param  mixed $data Data to encode into a formatted JSON string
     * @param  int $options json_encode options (defaults to JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
     *
     * @return string Encoded json
     */
    public static function encode($data, $options = null)
    {
        if ($data === false)
            return 'false';

        if (is_null($options)) 
            $options = 448;        

        if ($options & static::JSON_UNESCAPED_UNICODE) 
            static::_forceutf8($data);        

        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            $json = json_encode($data, $options);
            if ($json === false)
                throw new Exception("pretty 1: Nie powiodło się przekształcenie za pomocą json_encode".print_r($data, true));
        }

        $json = json_encode($data);
        if ($json === false)
            throw new Exception("pretty 2: Nie powiodło się przekształcenie za pomocą json_encode".print_r($data, true));

        $prettyPrint = (bool)($options & self::JSON_PRETTY_PRINT);
        $unescapeUnicode = (bool)($options & self::JSON_UNESCAPED_UNICODE);
        $unescapeSlashes = (bool)($options & self::JSON_UNESCAPED_SLASHES);

        if (!$prettyPrint && !$unescapeUnicode && !$unescapeSlashes) 
            return $json;        

        $result = '';
        $pos = 0;
        $strLen = strlen($json);
        $indentStr = '    ';
        $newLine = "\n";
        $outOfQuotes = true;
        $buffer = '';
        $noescape = true;

        for ($i = 0; $i < $strLen; $i++) {
            // Grab the next character in the string
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ('"' === $char && $noescape) 
                $outOfQuotes = !$outOfQuotes;            

            if (!$outOfQuotes) {
                $buffer .= $char;
                $noescape = '\\' === $char ? !$noescape : true;
                continue;
            } elseif ('' !== $buffer) {

                if ($unescapeSlashes) 
                    $buffer = str_replace('\\/', '/', $buffer);                

                if ($unescapeUnicode && function_exists('mb_convert_encoding')) {
                    // http://stackoverflow.com/questions/2934563/how-to-decode-unicode-escape-sequences-like-u00ed-to-proper-utf-8-encoded-cha
                    $buffer = preg_replace_callback('/(\\\\+)u([0-9a-f]{4})/i', function ($match) {
                        $l = strlen($match[1]);

                        if ($l % 2) 
                            return str_repeat('\\', $l - 1) . mb_convert_encoding(pack('H*', $match[2]), 'UTF-8',
                                'UCS-2BE');                        

                        return $match[0];
                    }, $buffer);
                }

                $result .= $buffer . $char;
                $buffer = '';
                continue;
            }
            if (':' === $char) {
                // Add a space after the : character
                $char .= ' ';
            } elseif (('}' === $char || ']' === $char)) {

                --$pos;
                $prevChar = substr($json, $i - 1, 1);

                if ('{' !== $prevChar && '[' !== $prevChar) {
                    // If this character is the end of an element,
                    // output a new line and indent the next line
                    $result .= $newLine;
                    for ($j = 0; $j < $pos; $j++) {
                        $result .= $indentStr;
                    }
                } else {
                    // Collapse empty {} and []
                    $result = rtrim($result) . "\n\n" . $indentStr;
                }
            }
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line
            if (',' === $char || '{' === $char || '[' === $char) {

                $result .= $newLine;

                if ('{' === $char || '[' === $char) 
                    ++$pos;                

                for ($j = 0; $j < $pos; $j++) 
                    $result .= $indentStr;                
            }
        }

        if ($result === false)
            throw new Exception("after processing: Nie powiodło się przekształcenie za pomocą json_encode".print_r($data, true));

        return $result;
    }

    protected static function _forceutf8(&$data)
    {
        if (is_array($data)) {
            foreach ($data as &$d) {
                if (is_string($d)) {
                    $t = @iconv('UTF-8', 'UTF-8', $d);
                    
                    if ($t === false) 
                        $d = utf8_encode($d);                    
                } elseif (is_array($d)) {
                    static::_forceutf8($d);
                }
            }
        }
    }

    public static function decode($json, $assoc = true, $depth = 512)
    {
        return json_decode($json, $assoc, $depth);
    }
}
