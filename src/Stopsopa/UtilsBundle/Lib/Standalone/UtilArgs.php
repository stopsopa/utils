<?php

namespace Stopsopa\UtilsBundle\Lib\Standalone;

/**
 *
    $c = new UtilArgs(array(
        'TEST',
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
        'testmały',
        array('drugi array','trzy'),
        function () {
            echo 'dwa';
        },
        new stdClass(),
        56,
        new DateTime(),
    ));

    niechginie($c->get(UtilArgs::ARR));
    nieginie($c->pop(UtilArgs::STRING));
    niechginie($c->get(UtilArgs::ARR | UtilArgs::STRING | UtilArgs::NUL));
 */
class UtilArgs
{
    protected $args;
    const NUL       = 1;
    const INT       = 2;
    const STRING    = 4;
    const ARR       = 8;
    const RESOURCE  = 16;
    const CALLBACK  = 32;
    const FLOAT     = 64;
    const OBJECT    = 128;
    const NUMERIC   = 256;


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
    public function &pop($type) {
        $list = array();

        foreach ($this->args as $key => &$d) {
            if ($this->_isType($d, $type)) {
                unset($this->args[$key]);
                $list[] = $d;
            }
        }

        return $list;
    }
    public function &getFirst($type, &$default = null) {
        $list = array();

        foreach ($this->args as $key => &$d) {
            if ($this->_isType($d, $type)) {
                return $d;
            }
        }

        return $list;
    }
    public function &popFirst($type, &$default = null) {
        $list = array();

        foreach ($this->args as $key => &$d) {
            if ($this->_isType($d, $type)) {
                unset($this->args[$key]);
                return $d;
            }
        }

        return $list;
    }
    protected function _isType(&$d, $type) {
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
        if (is_string($type) && is_object($d) && $d instanceof $type) {
            return true;
        }
        return false;
    }
}