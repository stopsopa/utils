<?php

namespace Stopsopa\UtilsBundle\Services;

use Exception;

/**
 * Pisane z api wersja: JIRA 6.4.8 REST API
 *     https://docs.atlassian.com/jira/REST/6.4.8/.
 *
 * dostępne werjse to:
 *     https://docs.atlassian.com/jira/REST/
 *
 * Stopsopa\UtilsBundle\JiraRestService
 */
class JiraRestService
{
    const SERVICE = 'jirarest';
    protected $user;
    protected $password;
    protected $endpoint;
    public function __construct($config)
    {
        $this->endpoint = $config['endpoint'];
        $this->user = $config['user'];
        $this->password = $config['password'];
    }
    /**
     * @param string $url     - eg: /rest/api/2/project/CRAW/statuses
     * @param string $method  - GET(default)|POST|PUT|DELETE
     * @param array  $data
     * @param array  $headers
     *
     * @return array
     */
    public function curljson($url, $method = 'GET', $data = array(), $headers = array())
    {

        /**
         *
         * http://stackoverflow.com/a/3277224
         * Just tell cURL to decode the response automatically whenever it's gzipped         *
         * curl_setopt($ch,CURLOPT_ENCODING, ''); // dekodowanie automatyczne
         *
         *
         *
         */


        $url = $this->endpoint.$url;

        $method = strtoupper($method);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERPWD, $this->user.':'.$this->password);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        curl_setopt($ch, CURLOPT_URL, $url);

        if (count($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $headers = array_merge($headers, array(
            'Content-Type: application/json',
        ));

        if (count($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = null;
        $result = curl_exec($ch);

        curl_close($ch);

        $json = json_decode($result, true);

        if (count($json)) {
            return $json;
        }

        throw new Exception($result);
    }
}
