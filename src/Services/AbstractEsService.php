<?php

namespace Stopsopa\UtilsBundle\Services;

use Exception;

abstract class AbstractEsService {

    protected $endpoint;
    protected $index;
    public function __construct($url, $port = null, $index)
    {
        $this->endpoint = $url;

        if ($port) {

            $this->endpoint .= ':'.$port;
        }

        $this->index = $index;
    }
    public function delete($id) {
        return $this->api('DELETE', $id);
    }
    /**
     * @throws Exception
     * http://httpd.pl/bundles/toolssitecommon/tools/transform.php
     */
    public function api($method = null, $path = '', $data = array(), $headers = array())
    {
        if (!$method) {
            $method = 'GET';
        }

        $method = strtoupper($method);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_ENCODING, '');

//        curl_setopt($ch, CURLOPT_USERPWD, $this->user.':'.$this->password);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ( ! preg_match('#^https?://#', $path)) {

            $path = $this->endpoint . $path;
        }

        curl_setopt($ch, CURLOPT_URL, $path);

        if (!is_string($data) && $data) {
            $data = json_encode($data);
        }

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $tmpheaders = array(
            'Content-type' => 'application/json'
        );

        if (count($headers)) {

            $tmpheaders = array_merge($tmpheaders, $headers);
        }

        if (self::isAssoc($tmpheaders)) {

            $tmp = array();

            foreach ($tmpheaders as $key => $value) {
                if (is_numeric($key)) {
                    $tmp[] = $value;
                }
                else {
                    $tmp[] = "$key: $value";
                }
            }

            $tmpheaders = $tmp;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $tmpheaders);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//        curl_setopt($ch, CURLOPT_VERBOSE, true); // good for debugging

        curl_setopt($ch, CURLOPT_HEADER, 1);

        $response = null;

        $response = curl_exec($ch);

        // Then, after your curl_exec call:
        $header_size    = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $header         = substr($response, 0, $header_size);

        $body           = substr($response, $header_size);

//        die(var_dump($body));

        $res = array();

        $res['body']   = json_decode($body, true) ?: $body;

        $res['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($res['status'] === 0) {

            throw new Exception("Unable to connect to elasticsearch {$this->endpoint}");
        }

        curl_close($ch);

        $header = explode("\n", $header);

        $hlist = array();
        foreach ($header as &$d) {

            $dd = explode(':', $d, 2);

            if (count($dd) === 2) {
                $hlist[$dd[0]] = trim($dd[1]);
            }
        }

        $res['header'] = $hlist;


        $res['request-path'] = $path;
        $res['request-method'] = $method;
        $res['request-data'] = $data;
        $res['request-headers'] = $tmpheaders;

        return $res;
    }
    public static function isAssoc(&$data)
    {
        $i = 0;
        foreach ($data as $k => $d) {
            if ($k !== $i++) {
                return true;
            }
        }
        return false;
    }
}
