<?php

namespace Stopsopa\UtilsBundle\Lib;
use Symfony\Component\HttpFoundation\Request as NativeRequest;

class Browser {
    /**
     * Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314 Safari/531.21.10
     */
    public static function isIpad(NativeRequest $request = null) {
        return static::_check($request, 'ipad');
    }
    public static function isIphone(NativeRequest $request = null) {
        return static::_check($request, 'iphone');
    }
    public static function isIphnoneOrIpad(NativeRequest $request = null) {
        return static::isIphone($request) || static::isIpad($request);
    }
    protected static function _check(NativeRequest $request = null, $str) {
        $agent = $request ? $request->headers->get('user-agent') : isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        return strpos(strtolower($agent), $str) !== false;
    }
}