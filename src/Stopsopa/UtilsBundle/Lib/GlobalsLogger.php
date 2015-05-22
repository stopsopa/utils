<?php

namespace Stopsopa\UtilsBundle\Lib;

/**
 *
$log = new GlobalsLogger('log.log');
$log->write();
 */
class GlobalsLogger {
    protected $file;
    public function __construct($file) {
        $this->file = $file;
        if (!file_exists($file)) {
            $this->clear();
        }
    }
    public function clear () {
        file_put_contents($this->file, '');
    }
    protected function getData($label,$data) {
        $label = str_pad($label, 7,' ');
        $string = count($data) ? json_encode($data) : false;
        return $string ? "\n---$label : $string" : '';
    }
    private function log($data) {
        file_put_contents($this->file, $data . file_get_contents($this->file) );
    }
    public function write($mode = 'b', $add = '') {
        $time   = date('Y-m-d H:i:s');
        $client = $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : 'no user agent, probably file_get_contents()';

        $uri    = $_SERVER['REQUEST_URI'];

        $server = $this->getData('server', $_SERVER);
        $get    = $this->getData('get', $_GET);
        $post   = $this->getData('post', $_POST);
        $cookie = $this->getData('cookie', $_COOKIE);
        switch ($mode) {
            case 'b':
                $add = base64_decode($add);
                $data = $add;
                if ($data = json_decode($data)) {
                    if (is_string($data)) {
                        $add = $data;
                    }
                }
                break;
            case 'u':
                $add = rawurldecode($add);
                break;
            default :
        }
        $add    = $add ? "\n---data   :>>>$add<<<" : '';


        $this->log(<<<google
---time: $time ---client: $client
---uri : $uri$server$get$post$cookie$add


google
        );
    }
}

