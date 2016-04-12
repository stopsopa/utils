<?php

namespace Stopsopa\UtilsBundle\Lib;
use SimpleXMLElement;
use Exception;
use stdClass;

class SimpleXMLElementHelper {
    /**
     * Because working with pure SimpleXMLElement is really shitty
     * source: http://php.net/manual/en/class.simplexmlelement.php#111394
     */
    public static function normalize(SimpleXMLElement $xml, $force = false, $addNative = false) {

        $obj = new StdClass();

        $obj->name = $xml->getName();

        $text = trim((string)$xml);
        $attributes = array();
        $children = array();

        if ($addNative) {
            $obj->native = $xml;
        }

        foreach($xml->attributes() as $k => $v){
            $attributes[$k]  = (string)$v;
        }

        foreach($xml->children() as $k => $v){
            $children[] = static::normalize($v, $force);
        }

        if($force or $text !== '')
            $obj->text = $text;

        if($force or count($attributes) > 0)
            $obj->attributes = $attributes;

        if($force or count($children) > 0)
            $obj->children = $children;

        return $obj;
    }
    public static function parseFile($file) {

        if (!file_exists($file)) {
            throw new Exception("File '$file' doesn't exists");
        }

        if (!is_readable($file)) {
            throw new Exception("File '$file' is not readdable");
        }

        return static::parseString(file_get_contents($file));
    }
    public static function parseString($xml) {

        $libxml_previous_state = libxml_use_internal_errors(true);

        /* @var $xml SimpleXMLElement */
        $xml = new SimpleXMLElement($xml, LIBXML_ERR_NONE);

        $errors = libxml_get_errors();

        libxml_clear_errors();

        libxml_use_internal_errors($libxml_previous_state);

        return array(
            'xml' => $xml,
            'errors' => $errors ?: array()
        );
    }
}