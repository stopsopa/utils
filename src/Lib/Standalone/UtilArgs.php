<?php

namespace Stopsopa\UtilsBundle\Lib\Standalone;

/**
 *
    $c = new UtilArgs(array(
        'testmały',
        456,
        455.34,
        null,
        false,
        854382,
        564.4,
        function () {
            echo 'jeden';
        },
        null,
        true,
        array('pierwszy array','dwa'),
        'TEST',
        array('drugi array','trzy'),
        function () {
            echo 'dwa';
        },
        new stdClass(),
        56,
        new DateTime(),
    ));


    nieginie($c->shiftFirst(UtilArgs::NUL, 'default'));
    nieginie($c->shiftFirst(UtilArgs::NUL, 'default'));
    nieginie($c->shiftFirst(UtilArgs::NUL, 'default'));
    nieginie($c->shiftFirst(UtilArgs::NUL, 'default'));
    nieginie($c->shiftFirst(UtilArgs::NUL, 'default'));
    nieginie($c->shiftFirst(UtilArgs::STRING | UtilArgs::ARR));
    nieginie($c->get(UtilArgs::STRING | UtilArgs::ARR));
    niechginie($c->shift(UtilArgs::STRING | UtilArgs::ARR));
 */
class UtilArgs
{
    const NUL       = 1;
    const INT       = 2;
    const STRING    = 4;
    const ARR       = 8;
    const RESOURCE  = 16;
    const CALLBACK  = 32;
    const FLOAT     = 64;
    const OBJECT    = 128;
    const NUMERIC   = 256;

    protected $args;

    public function __construct($args) {
        $this->args = $args;
    }
    /**
     * Wyciąga wszystkie elementy danego typu w fromie array, jeśli nie ma nic to zwróci tablicę pustą
     * Elementy w składowej args pozostają tam gdzie są
     * @param string $type - int|string|array|resource|callback|float|object|numeric|[namespace]
     * @param type $default
     */
    public function &get($type) {
        $list = array();

        foreach ($this->args as &$d) {
            if ($this->_isType($d, $type)) {
                $list[] = $d;
            }
        }

        return $list;
    }
    /**
     * Wyciąga wszystkie elementy danego typu w fromie array, jeśli nie ma nic to zwróci tablicę pustą
     * Elementy w składowej args zostają usunięte i zwrócone
     * @param string $type - int|string|array|resource|callback|float|object|numeric|[namespace]
     * @param type $default
     */
    public function &shift($type) {
        $list = array();

        foreach ($this->args as $key => &$d) {
            if ($this->_isType($d, $type)) {
                unset($this->args[$key]);
                $list[] = $d;
            }
        }

        return $list;
    }
    /**
     * Pobiera pierwszy element danego typu jeśli istnieje, jeśli nie to zwraca false,
     * lub wartość z opcjonalego parametru default
     *
     * Element w składowej args zostaje tam gdzie jest
     * @param type $type
     * @param mixed $default (parametr opcjonalny);
     * @return array
     */
    public function &getFirst($type) {

        foreach ($this->args as $key => &$d) {
            if ($this->_isType($d, $type)) {
                return $d;
            }
        }

        $args = func_get_args();
        if (count($args) > 1) {
            return $args[1];
        }

        $return = false;
        return $return;
    }
    /**
     * Pobiera pierwszy element danego typu jeśli istnieje, jeśli nie to zwraca false,
     * lub wartość z opcjonalego parametru default
     *
     * Element w składowej args zostaje usunięty i zwrócone
     * @param type $type
     * @param mixed $default (parametr opcjonalny);
     * @return array
     */
    public function &shiftFirst($type) {

        foreach ($this->args as $key => &$d) {
            if ($this->_isType($d, $type)) {
                unset($this->args[$key]);
                return $d;
            }
        }

        $args = func_get_args();

        if (count($args) > 1) {
            return $args[1];
        }

        $return = false;
        return $return;
    }
    protected function _isType(&$d, $type) {
        if ( (is_string($type) || is_object($type)) && is_object($d)) {
            return $d instanceof $type;
        }
        if ($type & static::STRING && is_string($d)) {
            return true;
        }
        if ($type & static::INT && is_int($d)) {
            return true;
        }
        if ($type & static::ARR && is_array($d)) {
            return true;
        }
        if ($type & static::FLOAT && is_float($d)) {
            return true;
        }
        if ($type & static::NUL && is_null($d)) {
            return true;
        }
        if ($type & static::NUMERIC && is_numeric($d)) {
            return true;
        }
        if ($type & static::OBJECT && is_object($d)) {
            return true;
        }
        if ($type & static::CALLBACK && is_callable($d)) {
            return true;
        }
        if ($type & static::RESOURCE && is_resource($d)) {
            return true;
        }
        return false;
    }
}
