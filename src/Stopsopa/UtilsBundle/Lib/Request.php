<?php

namespace Stopsopa\UtilsBundle\Lib;

use Symfony\Component\HttpFoundation\Request as CoreRequest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Stopsopa\UtilsBundle\Lib\Json\Json;

/**
 * Stopsopa\UtilsBundle\Lib\Request.
 */
class Request extends CoreRequest
{
    /**
     * Jeśli dane zostaną wysłane json to zostaną automatycznie zdeserializowane i upakowane w obiekcie
     * ParameterBag jak reszta pozostałych parametrów i zostaną upchane w składowej json tego obiektu.
     *
     * UWAGA: stan tego obiektu jest niezależny od zadeklarowanego headera w responsie,
     * nie musi być header Content-type: application/json aby została podjęta próba deserializaji json.
     * Funkcja json_decode jeśli dostanie nieprawidłowe dane do zdeserializowania to nie rzuca błedem tylko
     * zwraca null;
     *
     * @var ParameterBag
     */
    public $json;

//    protected static $isLangInitialized;


    public function initialize(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        parent::initialize($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->json = new ParameterBag(Json::decode($this->getContent()) ?: array());
    }
    public function getClientIp()
    {
        $ip = $this->server->get('HTTP_X_REAL_IP');

        if ($ip) {
            return $ip;
        }

        return parent::getClientIp();
    }
    protected static $domain;
    public static function getDomain()
    {
        if (!self::$domain) {
            // taki hack dla procesów cronowych bo one nie mają możliwości poznać domeny roboczej
        // dlatego wyciągnę specjalnie dla nich domenę z pliku konfiguracyjnego
          if (empty($_SERVER['HTTP_HOST'])) {
              //              $parser       = new Yaml();
//              $data         = $parser->parse(file_get_contents(__DIR__.'/../../../../app/config/hosts/'. getHost() .'.yml'));
//              $data = &$data['parameters'];
//              $_SERVER['HTTP_HOST']= $data['domain'];
//              ($data['protocol'] == 'https') and ($_SERVER['HTTPS'] = 'on');
              $_SERVER['HTTP_HOST'] = getParam('domain');
              (getParam('protocol') == 'https') and ($_SERVER['HTTPS'] = 'on');
          }

            self::$domain = isset($_SERVER['HTTPS']) && in_array($_SERVER['HTTPS'], array('https', 'on', '1')) ? 'https' : 'http';
            self::$domain = self::$domain.'://'.$_SERVER['HTTP_HOST'];
        }

        return self::$domain;
    }
    public static function isPost()
    {
        return isset($this) ? $this->getMethod() == 'POST' : isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST';
    }
    public static function isGet()
    {
        return isset($this) ? $this->getMethod() == 'GET' : isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET';
    }
    public static function isIe()
    {
        if (isset($this)) {
            $header = $this->headers->get('HTTP_USER_AGENT', '');
        } else {
            $header = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        }

        return strpos($header, 'MSIE') !== false;
    }
    public static function isCors()
    {
        if (isset($this)) {
            return $this->server->has('Origin') || $this->server->has('X-Origin');
        }

        return isset($_SERVER['HTTP_ORIGIN']) || isset($_SERVER['HTTP_X_ORIGIN']);
    }
    public static function isAjax()
    {
        if (isset($this)) {
            return $this->server->has('HTTP_X_REQUESTED_WITH');
        }

        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? true : false;
    }
    public function getHostname() {
        return getHost();  
    }
}
