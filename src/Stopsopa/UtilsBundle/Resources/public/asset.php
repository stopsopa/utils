<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mimelist = array(
  'css'   => 'text/css; charset=utf-8',
  'js'    => 'application/x-javascript; charset=utf-8',
  'eot'   => 'application/vnd.ms-fontobject',
  'woff'  => 'application/x-font-woff',
  'ttf'   => 'application/x-font-ttf',
  'svg'   => 'image/svg+xml',
  'png'   => 'image/png',
  'jpg'   => 'image/jpeg',
  'gif'   => 'image/gif'
);


$s = $_SERVER;
//print_r($s);

$block = '';
$target = substr($s['REQUEST_URI'], strlen($s['SCRIPT_NAME'])); 
$webdir = substr($s['SCRIPT_FILENAME'], 0, strlen($s['SCRIPT_FILENAME'])-strlen($s['SCRIPT_NAME']));
if (strpos($target, '=') !== false) {
  $target = explode('=', $target);
  $block  = $target[0];
  $target = '/'.$target[1];
}
$asset  = $webdir.$target;

if (strpos($asset, '?')) {
  $asset = explode('?', $asset);
  $asset = $asset[0];
}
//print_r($target);
//echo "\n";
//print_r($webdir);
//echo "\n";
//print_r($asset);
//echo "\n";
//print_r($block);
//echo "\n";

if (file_exists($asset)) {
  // ustawiam mime
  $ext   = trim(strtolower(pathinfo($asset, PATHINFO_EXTENSION)));
  if ($ext && array_key_exists($ext, $mimelist)) {  
    header("Content-type: $mimelist[$ext]");
    header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
    header('Pragma: no-cache');
//    $size = filesize($asset); // nie podaję bo jak podam to firefox nie doczytuje końcowych console.log() bo po doczytaniu zadeklarowanej długości kończy nasłuch
//    header("Content-Length: $size");
    // http://traxter-online.net/webmaster/naglowki-etag-i-expires-czyli-cachowanie-elementow-strony-w-przegladarce/
  }
  
  if (!is_readable($asset)) 
      throw new Exception("Plik $asset nie ma uprawnień do odczytu...");
  
  readfile($asset);
  
  if ($ext == 'js') {
      
    $log = ": $target";
    
    if (strlen($block)) 
      $log = "$block : $target";
    
    echo "\n;console && console.log && console.log('asset.php $log');";
  }
  
  return;
}
echo "File does not exist '$asset'";

header("HTTP/1.0 404 Not Found");






