<?php

/**
 * Odpalanie z linii komend /usr/bin/java -jar /usr/share/yui-compressor/yui-compressor.jar
 */
/**
 * Jak tutaj tworzyć pliki tymczasowe w tempie linux zobaczyć warto w symfony:
 * Assetic\Filter\Yui\BaseCompressorFilter
 */

error_reporting(E_ALL);
ini_set('display_errors',1);
header('Status: 200');

// java -jar /usr/share/yui-compressor/yui-compressor.jar jquery-1.9.1.js  --charset utf-8
// java -jar /usr/share/yui-compressor/yui-compressor.jar jquery-1.9.1.js -o jquery-1.9.1-min.js --charset utf-8
// handlebars $1 | java -jar /usr/share/yui-compressor/yui-compressor.jar --type js -o ${1/.handlebars/.js}
// cat jquery-1.9.1.js | java -jar /usr/share/yui-compressor/yui-compressor.jar --type js -o jquery-1.9.1-min.js
// echo "(function (t) {console.log(t)})('test')" | java -jar /usr/share/yui-compressor/yui-compressor.jar --type js -o jquery-1.9.1-min.js

/**
 * Warto też poczytać to: 
 *  http://blog.yjl.im/2013/01/running-remote-yui-compressor-and.html
 *  g(Running remote YUI Compressor and Google Closure Compiler)
   curl -L -F type=CSS -F redirect=1 -F 'compressfile[]=@style.css; filename=style.css' -o style.min.css http://refresh-sf.com/yui/
   przez strumień (pipe, stream):
     curl -L -F type=CSS -F redirect=1 -F 'compressfile[]=@-; filename=style.css' -o style.min.css http://refresh-sf.com/yui/ < style.css
     cat style.css | curl -L -F type=CSS -F redirect=1 -F 'compressfile[]=@-; filename=style.css' -o style.min.css http://refresh-sf.com/yui/
     curl -L -F type=JS -F redirect=1 -F 'compressfile[]=@-; filename=jquery.css' -o jquery.min.js http://refresh-sf.com/yui/ < jquery.js
     cat jquery.js | curl -L -F type=JS -F redirect=1 -F 'compressfile[]=@-; filename=jquery.css' -o jquery.min.js http://refresh-sf.com/yui/
   @- powoduje że czyta ze strumienia
 */
$java = '/usr/bin/java'; // lub po prostu java
$yui  = '/usr/share/yui-compressor/yui-compressor.jar';
// jeśli ścieżki nie są prawidłowe to podejmuję próbę ustalenia ścieżek automatycznie
if (req(dirname(__FILE__).'/../../../../../../app/getHost.php')) {
  if ($yml = req(dirname(__FILE__).'/../../../../../../app/config/hosts/'.getHost().'.yml')) {
    $yui  = preg_replace('#^.*?assetic\.filters\.yui_js\.jar\s*:\s*([^\s]*).*$#is','$1',$yml);
    $java = preg_replace('#^.*?assetic\.java\.bin\s*:\s*([^\s]*).*$#is','$1',$yml);
  }
}

if (isset($_POST['data'])) { 
  //$file = dirname(__FILE__).'/'.substr(md5(uniqid()),0,10).'.tmp';
  $file = tempnam(realpath(sys_get_temp_dir()), 'YUI-SERVICE-');
  file_put_contents($file, $_POST['data']);
  
  if (empty($_GET['mode']) || !preg_match('#^(js|css)$#', $_GET['mode'])) {
    header("HTTP/1.0 404 Not Found");
    debugg('mode is not valid');
  }
  
  $cmd = "$java -jar $yui $file --type {$_GET['mode']} --charset utf-8";
  // http://php.net/manual/en/function.proc-open.php
  $descriptorspec = array(
     0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
     1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
     2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
  );
  $cwd = '/tmp';
  $env = array('some_option' => 'aeiou');
  $process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);
  
  if (is_resource($process)) {
    proc_close($process);
    echo fread($pipes[0]);    
  }
  unlink($file);
  exit();
}
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <title></title>
  <style type="text/css">
    textarea {
      width: 90%;      
    }
    #source {
      height: 200px;
    }    
    #target {
      height: 1000px;
    }
    span {
      display: none;
    }
  </style>
  <script type="text/javascript" src="//code.jquery.com/jquery-1.9.1.min.js"></script>
  <script type="text/javascript">
    $(function () {      
      var s = $('span');
      $('.go').click(function () {
        var that = $(this);
        s.show();
        $.ajax(location.pathname+'?mode='+that.html(),{
          type: 'post',
          data: {
            data: $('#source').val()
          }
        })
        .done(function (data) {
          $('#target').val(data);
        })
        .always(function () {
          s.hide();
        });                
      });
    });
  </script>  
</head> 
<body>
  <button class="go">js</button> <button class="go">css</button><span>loading...</span><br />
  <textarea id="source">
style {
  display: none;
  /* test */
}
/*!
 * test
 */
'1';
'2';
'ąśżźćęńół';
  </textarea>
  <p>result:</p> 
  <textarea id="target"></textarea>
</body>
</html>







<?php

/**
 * <pre>
 * debug(array())      - bez pre,   print_r
 * debug(array(),1)    - z   pre,   print_r 
 * debug(array(),11)   - z   pre,   var_dump
 * debug(array(),-1)   - bez pre,   var_dump 
 * debug(array(),'01') - bez pre,   var_dump
 * </pre>
 * @param mixed $data
 * @param string|int $mode
 * @param string $comment 
 */
function debug($data,$mode=0,$comment='',$b='&bull;') {
  if (!isset($_COOKIE['debug'])) return;
  $comment = $comment ? "$b$b$b$comment$b$b$b\n" : '' ;
  $mode = str_pad($mode, 2, '0');
  if ($mode[0]==1) echo "<pre>$comment";
  ($mode[1] == 0) ? print_r($data) : var_dump($data);
}
function debugg($data,$mode=0,$comment='',$b='&bull;') {
  debug($data,$mode,$comment,$b); die();
}

function req($file) {
  if (file_exists($file)) {
    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'php') {
      require_once $file;
      return true;
    }
    else {
      return file_get_contents($file);
    }
  }
  return false;
}