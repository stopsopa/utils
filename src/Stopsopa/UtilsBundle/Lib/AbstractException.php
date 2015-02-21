<?php

namespace Stopsopa\UtilsBundle\Lib;
use Exception;

class AbstractException extends Exception {
    /**
     * Sprawia że od momentu wywołania tej metody wszystkie 'notice' i 'warning' php będą sygnalizowane przez mechanizm wyjątków 
     * @throws Exception
     */
    public static function setErrorHandler() {
	set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line, array $err_context) {
	    throw new Exception("$err_msg, file: $err_file, line: $err_line");	  
	});
    }
    /**
     * Przywraca normalny sposób zgłaszania 'notice' i 'warning' php, od teraz nie będą zgłaszane przez mechanizm wyjątków
     */
    public static function delErrorHandler() {
	restore_error_handler();
    }
    
}

