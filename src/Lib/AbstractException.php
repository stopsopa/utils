<?php

namespace Stopsopa\UtilsBundle\Lib;

use Exception;

class AbstractException extends Exception
{
    public static function setErrorHandler()
    {
        set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line, array $err_context) {
            throw new Exception("$err_msg, file: $err_file, line: $err_line");
        });
    }
    public static function delErrorHandler()
    {
        restore_error_handler();
    }
}
