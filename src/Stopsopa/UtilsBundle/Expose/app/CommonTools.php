<?php

require_once __DIR__.'/../vendor/autoload.php';
//require_once __DIR__.'/getHost.php';

use Symfony\Component\Yaml\Yaml;
//use App;

class CommonTools {
//  public static function getPdo() {
//    if (!self::$pdo) {
//        try {
//            $driver = self::getParam('database_driver');
//            $driver = str_replace('pdo_', '', $driver);
//
//            self::$pdo = new PDO(
//                    $driver.
//                    ':host='.
//                    self::getParam('database_host').
//                    ';name='.
//                    self::getParam('database_name'),
//                    self::getParam('database_user'),
//                    self::getParam('database_password'),
//                    array(1002 => "SET NAMES 'UTF8'")
//             );
//        } catch (PDOException $e) {
//            print "Error!: " . $e->getMessage() . "<br/>";
//            die();
//        }
//    }
//
//    return self::$pdo;
//  }
}
if (extension_loaded('xdebug')) {
    ini_set('xdebug.var_display_max_depth', 5);
    ini_set('xdebug.var_display_max_children', 256);
    ini_set('xdebug.var_display_max_data', 8024);
}


if (!function_exists('isdebug')) { // przez to mogę tą funkcję zdefiniować w pierwsze kolejnosci w front kontrolerach
    function isdebug() {
      $allowed = false;
      if (isset($_COOKIE['debug'])) $allowed = true;
      if (php_sapi_name() == 'cli')  $allowed = true;
      return $allowed;
    }
}

/**
 * Inspiracja: https://www.youtube.com/watch?v=P17pg55FbvA
 * @param mixed $data
 * @param string|int $mode
 *   1: [-012]: (def: 0), -0: ::dump(), 1: var_dump(), 2: print_r()
 *   2: [01]  : (def: 0), 0: pre, 1: nopre
 * @param integer $maxDepth
 * @param bool $return - def: false, czy ma zwrócić wynik zamiast wyprintować na ekran
 * @return string
 */
function nieginie($data, $mode = null, $maxDepth = 2, $return = false, $force = false, $trace = null) {
  if ($force || isdebug()) {
    $trace or ($trace = debug_backtrace());
    $trace = array_shift($trace);
    $return and ob_start();
    if (is_null($mode)) $mode = '00';
    $mode = str_pad($mode, 2, '0');
    if ($mode[1] == 0) echo "<pre>";
    echo $trace['file'].':'.$trace['line'].PHP_EOL;
    $k = $mode[0];
    if (in_array($k, array('-','0','1'))) {
      if (in_array($k, array('-','0')) && class_exists('Doctrine\Common\Util\Debug')) {
//        echo '--== Uwaga Debug::dump() wycina znaczniki html ==--'.PHP_EOL;
          echo 'Debug::dump()'.PHP_EOL;
          ob_start();
          \Doctrine\Common\Util\Debug::dump($data, $maxDepth, true);
          echo ob_get_clean();
      }
      else {
        echo 'var_dump()'.PHP_EOL;
        var_dump($data);
      }
    }
    else {
      echo 'print_r()'.PHP_EOL;
      print_r($data);
    }
    return $return ? ob_get_clean() : null;
  }
}
/**
 * @param mixed $data
 * @param string|int $mode
 *   1: [-012]: (def: 0), -0: ::dump(), 1: var_dump(), 2: print_r()
 *   2: [01]  : (def: 0), 0: pre, 1: nopre
 * @param integer $maxDepth
 * @param bool $return - def: false, czy ma zwrócić wynik zamiast wyprintować na ekran
 * @return string
 */
function niechginie($data, $mode = null, $maxDepth = 2, $return = false, $force = false) {
  nieginie($data, $mode, $maxDepth, $return, false, debug_backtrace());
  ($force || isdebug()) and die();
}
/**
 * @param mixed $data
 * @param string|int $mode
 *   1: [-012]: (def: 0), -0: ::dump(), 1: var_dump(), 2: print_r()
 *   2: [01]  : (def: 0), 0: pre, 1: nopre
 * @param integer $maxDepth
 * @param bool $return - def: false, czy ma zwrócić wynik zamiast wyprintować na ekran
 * @return string
 */
function iniechginie($data, $mode = null, $maxDepth = 2, $return = false, $force = false) {
  niechginie($data, $mode, $maxDepth, $return, $force = true, debug_backtrace());
}
/**
 * @param mixed $data
 * @param string|int $mode
 *   1: [-012]: (def: 0), -0: ::dump(), 1: var_dump(), 2: print_r()
 *   2: [01]  : (def: 0), 0: pre, 1: nopre
 * @param integer $maxDepth
 * @param bool $return - def: false, czy ma zwrócić wynik zamiast wyprintować na ekran
 * @return string
 */
function iniechginiee($data, $mode = null, $maxDepth = 2, $return = false) {
  nieginie($data, $mode, $maxDepth, $return, $force = true, debug_backtrace());
}



if(!function_exists('str_putcsv'))
{
    function str_putcsv($input, $delimiter = ',', $enclosure = '"')
    {
        // Open a memory "file" for read/write...
        $fp = fopen('php://temp', 'r+');
        // ... write the $input array to the "file" using fputcsv()...
        fputcsv($fp, $input, $delimiter, $enclosure);
        // ... rewind the "file" so we can read what we just wrote...
        rewind($fp);
        // ... read the entire line into a variable...
        $data = fread($fp, 1048576);
        // ... close the "file"...
        fclose($fp);
        // ... and return the $data to the caller, with the trailing newline from fgets() removed.
        return rtrim($data, "\n");
    }
}

function isdebug() {
    return !empty($_COOKIE['debug']) && $_COOKIE['debug'] !== 'null';
}

function getHost() {
    return php_uname('n');
}