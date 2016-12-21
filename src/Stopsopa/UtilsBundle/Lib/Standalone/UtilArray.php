<?php

namespace Stopsopa\UtilsBundle\Lib\Standalone;

use ArrayAccess;
use Stopsopa\UtilsBundle\Lib\Exception\UtilArrayException;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Validator\Constraints\Callback;
use Traversable;

/**
 * Stopsopa\UtilsBundle\Lib\UtilArray.
 */
class UtilArray
{
    /**
     * Zpłaszcza tablicę.
     *
     * @param type $data
     *
     * @return type
     */
    public static function flat($data)
    {
        $d = array();

        return self::_flat($d, $data);
    }
    /**
     * Pomocnicza metoda dla flat.
     *
     * @param type $target
     * @param type $data
     * @param type $key
     *
     * @return type
     */
    protected static function &_flat(&$target, &$data, $key = '')
    {
        foreach ($data as $k => $d) {
            if (is_array($d)) {
                self::_flat($target, $d, $key.'.'.$k);
            } else {
                $target[ltrim($key.'.'.$k, '.')] = $d;
            }
        }

        return $target;
    }
    public static function sortKeysRecursive(&$data, $param = 0, $reverse = false) {

        if (is_array($data)) {

            if ($reverse) {
                krsort($data, $param ?: SORT_REGULAR);
            }
            else if (is_callable($param)) {
                uksort($data, $param);
            }
            else {
                ksort($data, $param ?: SORT_REGULAR);
            }

            foreach ($data as &$d) {
                static::sortKeysRecursive($d, $param, $reverse);
            }
        }
        
    }
    public static function &serialize(array $array)
    {
        $array = http_build_query($array);

        return $array;
    }

    public static function arrayMergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1; // kopiowanie - aby nie działać na tej samej tablicy

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged [$key]) && is_array($merged [$key])) {
                $merged [$key] = self::arrayMergeRecursiveDistinct($merged [$key], $value);
            } else {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }

    public static function compare($val1, $val2)
    {
        if (is_string($val1)) {
            return $val1 === $val2;
        }

        if (is_array($val1) && is_array($val2)) {
            return !count(self::arrayDiffAssocRecursive($val1, $val2));
        }

        return $val1 === $val2;
    }
    /**
     * http://www.php.net/manual/de/function.array-diff-assoc.php#111675.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    public static function arrayDiffAssocRecursive(array $array1, array $array2)
    {
        $difference = array();
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = self::arrayDiffAssocRecursive($value, $array2[$key]);
                    if (!empty($new_diff)) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                $difference[$key] = $value;
            }
        }

        return $difference;
    }
    /**
     * Sprawdza czy tablica jest regularną tablica asocjacyjną
     * Wystarczy jedna wyrwa w kluczach mimo iż są integerami,
     * a już zostanie zwrócona wartość true.
     *
     * @param type $data
     *
     * @return bool
     */
    public static function isAssoc(&$data)
    {
        $i = 0;
        foreach ($data as $k => $d) {
            if ($k !== $i++) {
                return true;
            }
        }

        return false;
    }
    /**
     * Bierze co drugi element z listy, zaczynając od pierwszego
     * (pierwszy załącza do wynikowej tablicy).
     *
     * @param array $array
     * @param int   $take  (dev) - co który ma brać?
     *
     * @return array
     */
    public static function even($array, $take = 2)
    {
        $data = array();
        $i = 0;
        if (is_array($array)) {
            foreach ($array as $k => $d) {
                if (($i++ % $take) === 0) {
                    if (is_string($k)) {
                        $data[$k] = $d;
                    } else {
                        $data[] = $d;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $source
     * @param null $key
     * @param $default = undefined : throw exception, mixed : return if can't find
     * @return mixed
     */
    public static function cascadeGet(&$source, $key = null)
    {
        if ($key === null) {
            return $source;
        }

        $keys = self::cascadeExplode($key);
        $element = &$source;
        while (($d = array_shift($keys)) !== null) {
            if (isset($element[$d])) {
                if (count($keys)) {
                    $element = &$element[$d];
                    continue;
                } else {
                    return $element[$d];
                }
            } else {
                if (func_num_args() < 3) {
                    throw new Exception("Can't find inner element '$key'");
                }

                return func_get_arg(2);
            }
        }

        if (func_num_args() < 3) {
            throw new Exception("Can't find element '$key'");
        }

        return func_get_arg(2);
    }

    /**
     * @param string $key
     *  also works keys like '..k.' or 'k.t...'
     * @param mix    $val
     *
     * @return stack of generated keys
     */
    public static function cascadeSet(&$source, $key, $val, $returnOnlyLasGeneratedKey = true)
    {
        if (!is_string($key)) {
            return false;
        }

        $stack = array();

        $key = static::cascadeExplode($key);

        $element = &$source;
        while (($d = array_shift($key)) !== null) {
            if (count($key)) {
                if (strlen($d)) {
                    if (!(isset($element[$d]) && is_array($element[$d]))) {
                        $element[$d] = array();
                    }
                    $element = &$element[$d];
                }
                else {
                    $element[] = array();
                    $generatedKey = count($element)-1;
                    $stack[] = $generatedKey;
                    $element = &$element[$generatedKey];
                }
            } else {
                if (strlen($d)) {
                    $element[$d] = $val;
                }
                else {
                    $generatedKey = count($element);
                    $stack[] = $generatedKey;
                    $element[] = $val;
                }

                if ($returnOnlyLasGeneratedKey) {

                    return array_pop($stack);
                }

                return $stack;
            }
        }

        if ($returnOnlyLasGeneratedKey) {

            return array_pop($stack);
        }

        return $stack;
    }
    public static function cascadeExplode($key)
    {
        $key = preg_split("#(?<!\\\\)\.#", $key);

        foreach ($key as $k => &$d) {
            $d = str_replace('\\.', '.', $d);
        }

        return $key;
    }

    /**
     * Dosyć przydatne jest to że jesli usuniemy jakiś pośredni węzeł to tak na prawdę zostanie on przywrócony do stanu pierwotnego: default.
     *
     * @param string $key
     *
     * @return bool|string
     */
    public static function cascadeRemove(&$source, $key)
    {
        if (!$key || !is_string($key)) {
            return false;
        }

        $key = static::cascadeExplode($key);
        $element = &$source;
        while (($d = array_shift($key)) !== null) {
            if (isset($element[$d])) {
                if (count($key)) {
                    $element = &$element[$d];
                    continue;
                } else {
                    unset($element[$d]);

                    return true;
                }
            } else {
                return true;
            }
        }

        return true;
    }
    public static function readAssocStringToArray($string, $freeToVal = true)
    {
        $string = $string.'';

        $data = array();
        foreach (explode("\n", $string) as $k => $d) {
            $t = trim($d);

            if ($t) {
                $data[] = $t;
            }
        }

        $list = array();
        foreach ($data as $d) {
            if ($d === ':') {
                continue;
            }
            $d = preg_split('#(?<!\\\\):#', $d, 2);

            $d[0] && ($d[0] = trim(str_replace('\:', ':', $d[0])));
            isset($d[1]) && ($d[1] = trim($d[1]));
            if (empty($d[0]) && isset($d[1])) {
                $d[0] = $d[1];
                $d[1] = '';
            }
            if (!isset($d[1])) {
                if ($freeToVal) {
                    $list[] = $d[0];
                } else {
                    $list[$d[0]] = '';
                }
            } else {
                $d[1] = trim($d[1]);
                $list[$d[0]] = $d[1];
            }
        }

        return $list;
    }
    /**
     * Sprawdza czy obiekt można przejść foreach.
     *
     * @param ArrayAccess $object
     *
     * @return bool
     */
    public static function isForeachable(&$object)
    {
        return is_array($object) || $object instanceof Traversable;
    }
    public static function isArrayAccessable(&$object)
    {
        return is_array($object) || $object instanceof ArrayAccess;
    }
    /**
     * Jeśli obiekt implementuje ArrayAccess co oznacza że można go używać jak tablicy
     * $k['test'] = 'test';
     * to wcale nie oznacza że można go testować za pomocą array_key_exists.
     *
     * @param ArrayAccess|array $object
     * @param string|int        $key
     * @param bool              $throwExceptionIfNot
     *
     * @return bool
     *
     * @throws UtilArrayException
     */
    public static function offsetExists(&$object, $key, $throwExceptionIfNot = true)
    {
        if (is_array($object)) {
            if (array_key_exists($key, $object)) {
                return true;
            }
        } elseif ($object instanceof ArrayAccess) {
            if ($object->offsetExists($key)) {
                return true;
            }
        }

        if ($throwExceptionIfNot) {
            throw new UtilArrayException("Offset '".$key."' not exist");
        }

        return false;
    }
}
