<pre><?php

error_reporting(E_ALL);
ini_set('display_errors',1);

$ch = curl_init ('http://www.legenhit.com/yui-service/service.php?mode=js');
curl_setopt ($ch, CURLOPT_POST, true);
curl_setopt ($ch, CURLOPT_POSTFIELDS, array (
  "data" => file_get_contents('../src/Core/UberBundle/Resources/public/js/jquery-jtemplates-0.8.js') 
));
curl_setopt ($ch, CURLOPT_HEADER, false);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

echo curl_exec ($ch);      