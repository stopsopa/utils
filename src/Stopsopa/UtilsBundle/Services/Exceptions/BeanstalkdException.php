<?php

namespace Stopsopa\UtilsBundle\Services\Exceptions;
use Exception;

/**
 * Stopsopa\UtilsBundle\Services\Exceptions\BeanstalkdException
 */
class BeanstalkdException extends Exception {
    const TIMED_OUT       = 1;
    const DELETE_ERROR    = 2;
    const PUT_ERROR       = 3;
    const PARSE_ERROR     = 4;
    const RESERVE_ERROR  = 5;
    const BURY_ERROR      = 6;
    const EMPTYDATA_ERROR    = 7;
    const EMPTYTUBE_ERROR    = 8;    
}