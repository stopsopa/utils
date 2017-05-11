<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mode = 'js';
$file = 'jquery-1.9.1.js';

$file = dirname(__FILE__)."/$file";
$out = $file."-min.$mode";

$url = "http://www.legenhit.com/yui-service/service.php?mode=$mode";

$post = array(
  'data' => file_get_contents($file),
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$mode == 'css' and header('Content-type: text/css');
$mode == 'js'  and header('Content-type: application/x-javascript');

echo curl_exec($ch);

/**
 * <pre>
 * debug(array())      - bez pre,   print_r
 * debug(array(),1)    - z   pre,   print_r 
 * debug(array(),11)   - z   pre,   var_dump
 * debug(array(),-1)   - bez pre,   var_dump 
 * debug(array(),'01') - bez pre,   var_dump
 * </pre>.
 *
 * @param mixed      $data
 * @param string|int $mode
 * @param string     $comment
 */
function debug($data, $mode = 0, $comment = '', $b = '&bull;')
{
    if (!isset($_COOKIE['debug'])) {
        return;
    }
    $comment = $comment ? "$b$b$b$comment$b$b$b\n" : '';
    $mode = str_pad($mode, 2, '0');
    if ($mode[0] == 1) {
        echo "<pre>$comment";
    }
    ($mode[1] == 0) ? print_r($data) : var_dump($data);
}
function debugg($data, $mode = 0, $comment = '', $b = '&bull;')
{
    debug($data, $mode, $comment, $b);
    die();
}
