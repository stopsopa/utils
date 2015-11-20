<?php

namespace Stopsopa\UtilsBundle\Services;
use Doctrine\DBAL\Connection;
use Stopsopa\UtilsBundle\Lib\Json\Json;

/**
 * Stopsopa\UtilsBundle\Services\ElasticSearch2
 * Class ElasticSearch2
 * @package Stopsopa\UtilsBundle\Services
 */
class ElasticSearch2 {
    /**
     * @var Connection
     */
    protected $dbal;
    protected $config;
    protected $eshost;
    protected $esport;
    protected $url;

    public function __construct(Connection $connection, $config, $eshost, $esport)
    {
        $this->dbal     = $connection;
        $this->config   = $config;
        $this->eshost   = $eshost;
        $this->esport   = $esport;
        $this->url      = $eshost.':'.$esport;
    }
    public function buildIndexes() {


    }
    protected function _transport($method = null, $path = '', $data = array(), $headers = array())
    {
        if (!$method) {
            $method = 'GET';
        }

        $method = strtoupper($method);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_ENCODING, '');

//        curl_setopt($ch, CURLOPT_USERPWD, $this->user.':'.$this->password);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        curl_setopt($ch, CURLOPT_URL, $this->url.$path);

        if (!is_string($data) && $data) {
            $data = Json::encode($data);
        }

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

//        $headers = array_merge($headers, array(
//            'Content-Type: application/json',
//        ));

        if (count($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_VERBOSE, true); // dobre do debugowania
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $response = null;
        $response = curl_exec($ch);

        // Then, after your curl_exec call:
        $header_size    = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header         = substr($response, 0, $header_size);
        $body           = substr($response, $header_size);

        $data = array();

        $data['body']   = Json::decode($body) ?: $body;
        $data['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $header = explode("\n", $header);

        $hlist = array();
        foreach ($header as &$d) {
            $dd = explode(':', $d, 2);
            if (count($dd) === 2) {
                $hlist[$dd[0]] = trim($dd[1]);
            }
        }

        $data['header'] = $hlist;

        return $data;
    }
}
