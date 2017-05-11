<?php

function setStatusCode($statusCode) {
    static $status_codes = null;
    if ($status_codes === null) {
        $status_codes = array (
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Temporarily Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
        );
    }
    if ($status_codes[$statusCode] !== null) {
        $status_string = $statusCode . ' ' . $status_codes[$statusCode];
        header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_string, true, $statusCode);
    }

    if ($statusCode == 503) {
        header('Retry-After: 7200',true); // in seconds // two hours
    }
}

setStatusCode(503);

?>

<!--
<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
    <style type="text/css">
        body {
            font-family: tahoma;
            text-align: center;
        }
        [data-container] {
            position: absolute;
            top: 50%;
            left: 50%;
            -moz-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            -o-transform: translate(-50%, -50%);
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%)
        }
    </style>
</head>
<body>
    <div data-container>
        Trwa aktualizacja serwisu. <br />
        Zapraszamy za kilka minut.
    </div>
</body>
</html>
-->


<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
    <style type="text/css">
        body {
            font-family: tahoma;
            text-align: center;
            height: 100%;
            width: 100%;
            margin: 0;
            position: fixed;
        }
        body > div {
            position: relative;
            width: 100%;
            height: 100%;
        }
        body > div > div {
            position: absolute;
            margin: auto;
            width:   300px;
            height:  46px;
            top:     -9999px;
            bottom:  -9999px;
            left:   -9999px;
            right:  -9999px;

            border: 1px solid red;
        }
    </style>
</head>
<body>
    <div>
        <div>
            <!-- przykład z: http://codepen.io/anon/pen/rOVOev?editors=110 -->
            Trwa aktualizacja serwisu.<br />Zapraszamy za kilka minut.
        </div>
    </div>
</body>
</html>




<?php

die();