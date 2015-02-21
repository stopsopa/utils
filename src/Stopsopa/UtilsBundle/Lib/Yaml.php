<?php

namespace Stopsopa\UtilsBundle\Lib;

use Symfony\Component\Yaml\Yaml as SfYaml;
use \Exception;

/**
 * Klasa do dumpowania array do yml
 * Powstałą gdyż klasa dostarczona z symfony nie dumpuje we wszystkich askpektach czytelnie dla człowieka
 */
class Yaml {
  protected static $spaces;
  public static function dump($data, $spaces = 2) {
    $spaces = (int)$spaces;
    $spaces = $spaces < 2 ? 2 : $spaces;
    self::$spaces = str_repeat(' ', $spaces);
    
    $ret = ''; 
    $assoc = self::isAssoc($data);
    
    foreach ($data as $k => $d)      
      $ret .= self::cycle($k, $d, $assoc); 
    
    return $ret;
  }
  protected static function cycle($key, $data, $assoc, $level = 0) {   
    $space = str_repeat(self::$spaces, $level);
    
    if ($assoc) {
      $ret = $space.self::checkColons($key).' : ';
    }
    else { 
      $ret = $space.'- ';
    }
    
    if (is_array($data)) {
      $i = true;      
      $assoc2 = self::isAssoc($data);
      foreach ($data as $k => $d) {
        $ret .= ($i ? "\n" : '').self::cycle($k, $d, $assoc2, $level+1);
        $i = false;
      }
    }
    else {
      if (is_string($data) && strpos($data, "\n") !== false) {
        $ret .= "|\n";
        $space = str_repeat(self::$spaces, $level + 1);
        foreach (explode("\n", $data) as $d) {
          $ret .= $space.$d."\n";
        }
      }
      else {
        switch (true) {
          case is_string($data):
            $ret .= self::checkColons($data);
            break;
          case is_null($data):
            $ret .= '~';
            break;
          case is_bool($data):
            $ret .= $data ? 'true' : 'false';
            break;
          case is_object($data):
            throw new Exception("Yaml:parse() value is an object. Class: ".get_class($data));
          default:
            $ret .= $data;
            break;
        }  
        $ret .= "\n";
      }
    }
    
    return $ret;
  }
  /**
   * Jeśli w podanym kluczu są dwukropki to otaczam cały string cudzysłowami
   * aby składnia w netbeans się nie sypała
   * @param type $text
   * @return type
   */
  protected static function checkColons($text) {
    
    if (   strpos($text, ':') !== false 
        || strpos($text, '[') !== false 
        || strpos($text, '{') !== false 
        || strpos($text, '-') !== false 
        || strpos($text, '<') !== false 
        || strpos($text, '&') !== false
    ) 
      return '"'.str_replace('"', '\"', $text).'"'; 
    
    return $text;
  }

  protected static function isAssoc($data) {
    $i = 0;
    foreach ($data as $k => $d) {
      if ($k !== $i++) 
         return true;      
    }
    return false;
  }
  /**
   * @var SfYaml 
   */
  protected static $sfparser;
  public static function parse($input, $exceptionOnInvalidType = false, $objectSupport = false) {
    
    if (!self::$sfparser) 
      self::$sfparser = new SfYaml();
    
    return self::$sfparser->parse($input, $exceptionOnInvalidType, $objectSupport);
  }
  /**
   * Parsuje plik w formacie yml
   * @param type $file
   * @param type $exceptionOnInvalidType
   * @param type $objectSupport
   * @return type
   */
  public static function parseFile($file, $exceptionOnInvalidType = false, $objectSupport = false) {
    
    if (!is_readable($file)) 
      throw new Exception("File '$file' is not readable");
    
    return self::parse(file_get_contents($file), $exceptionOnInvalidType, $objectSupport);
  }
}