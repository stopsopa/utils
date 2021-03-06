<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mimelist = array(
  'css' => 'text/css; charset=utf-8',
  'js' => 'application/x-javascript; charset=utf-8',
  'eot' => 'application/vnd.ms-fontobject',
  'woff' => 'application/x-font-woff',
  'ttf' => 'application/x-font-ttf',
  'svg' => 'image/svg+xml',
  'png' => 'image/png',
  'jpg' => 'image/jpeg',
  'gif' => 'image/gif',
);

$allowedpaths = array(
    '#^/bundles#',
);

// specjalna lista jeśli jakiś element pasuje to bezwzględnie zabraniamy
$forbidden = array(
    '#\.\.#',
);

$s = $_SERVER;
$g = $_GET;

$block = '';
$target = '/'.$g['asset'];
$webdir = substr($s['SCRIPT_FILENAME'], 0, strlen($s['SCRIPT_FILENAME']) - strlen($s['SCRIPT_NAME']));

$access = false;
// check access
foreach ($allowedpaths as &$allowed) {
    if (preg_match($allowed, $target)) {
        $access = true;
        break;
    }
}
foreach ($forbidden as &$forbid) {
    if (preg_match($forbid, $target)) {
        $access = false;
        break;
    }
}

if (!$access) {
    header('HTTP/1.1 401 Unauthorized');
    die("Unauthorized access to: '$target'");
}

$asset = $webdir.$target;

if (strpos($asset, '?')) {
    $asset = explode('?', $asset);
    $asset = $asset[0];
}

if (file_exists($asset)) {
    // ustawiam mime
  $ext = trim(strtolower(pathinfo($asset, PATHINFO_EXTENSION)));
    if ($ext && array_key_exists($ext, $mimelist)) {
        header("Content-type: $mimelist[$ext]");
        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        header('Pragma: no-cache');
//    $size = filesize($asset); // nie podaję bo jak podam to firefox nie doczytuje końcowych console.log() bo po doczytaniu zadeklarowanej długości kończy nasłuch
//    header("Content-Length: $size");
    // http://traxter-online.net/webmaster/naglowki-etag-i-expires-czyli-cachowanie-elementow-strony-w-przegladarce/
    }

    if (!is_readable($asset)) {
        throw new Exception("Plik $asset nie ma uprawnień do odczytu...");
    }

    readfile($asset);

    if ($ext == 'js') {
        $log = ": $target";

        if (strlen($block)) {
            $log = "$block : $target";
        }

        echo "\n;console && console.log && console.log('asset.php $log');";
    }

    return;
}
echo "File does not exist '$asset'";

header('HTTP/1.0 404 Not Found');
