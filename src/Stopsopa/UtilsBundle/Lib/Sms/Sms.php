<?php

namespace Stopsopa\UtilsBundle\Lib\Sms;

use Stopsopa\UtilsBundle\Lib\Standalone\UtilArray;
use App\Lib\Urlizer;

class Sms
{
    protected $url; // https://ssl.smsapi.pl/user.do
    protected $user;
    protected $pass;
    protected $from;
    protected $notify;
    protected $test;
    protected $result;
    protected $status;

    protected $params = [];
    protected $phones = [];

    /**
     * 3.2 Wysyłanie masowe spersonalizowanych wiadomości z wykorzystaniem parametrów
     * Istnieje możliwość wysłania do 100 spersonalizowanych wiadomości przy pomocy jednego wywołania
     * wykorzystuj.
     */
    const MULTIPLELIMIT = 100;
//    const MULTIPLELIMIT = 1;

    public function __construct($url, $user, $pass)
    {
        $this->url = $url;
        $this->user = $user;
        $this->pass = $pass;
    }
    /**
     * wysyłąnie:.
     *
     $message = 'Zaczynamy z talent days, twój bilet: [%1%]';
     $params = [
     '698404897' => [
     '1234-3'
     ],
     '669955237' => [
     '2345-4'
     ]
     ];
     
     
     niechginie($sms->sendMultipleMessages($message, $params), 2);
     *
     * @param type $message
     * @param type $params
     *
     * @return type
     *
     * Zwraca wartości typu,
     * Array
     */
    public function sendMultipleMessages($message, $params, $idx = null)
    {
                die('nie wysyłam teraz nic stop');
        $res = [];

        $data = [
            'message' => $message,
        ];

        $idx and ($data['idx'] = $idx);

        $this->clearMultipleMessages();

        foreach ($params as $tel => &$param) {
            if (!$this->canSetAnotherParameter()) {
                $r = $this->send($data);

                $res[] = [
                    'status' => $r,
                    'result' => $this->getLastResult(),
                ];

                $this->clearMultipleMessages();
            }
            $this->addParam($tel, $param);
        }

        if ($this->hasPhoneNumbers()) {
            $r = $this->send($data);

            $res[] = [
                'status' => $r,
                'result' => $this->getLastResult(),
            ];

            $this->clearMultipleMessages();
        }

        return $res;
    }
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }
    public function hasPhoneNumbers()
    {
        return (bool) count($this->phones);
    }

    public function setNotifyUrl($notify)
    {
        $this->notify = $notify;

        return $this;
    }
    public function setTest($mode)
    {
        $this->test = !!$mode;

        return $this;
    }
    public function clearMultipleMessages()
    {
        return $this->clearParams()->clearPhones();
    }
    public function clearParams()
    {
        $this->params = [];

        return $this;
    }
    /**
     * doc: Zalecana maksymalna ilość jednorazowej wysyłki (jedno wywołanie) wynosi 10 000 wiadomości metodą POST oraz do 200 wiadomości metodą GET.
     *
     * Tutaj zachowawczo zmniejszę limit do 700
     *
     * @return bool
     */
    public function canSetAnotherParameter()
    {
        if (count($this->params) < static::MULTIPLELIMIT) {
            return true;
        }

        return false;
    }

    public function clearPhones()
    {
        $this->phones = [];

        return $this;
    }
    /**
     * ->addParam('123456789',[
     *    'param1' => 'szymon',
     *    'param2' => 'pawel'
     * ]);.
     *
     * lub
     *
     * ->addParam('123456789',[
     *    'szymon',
     *    'pawel'
     * ]);
     *
     * @param string $to
     * @param array  $params
     */
    public function addParam($to, $params = [])
    {
        if (!$this->canSetAnotherParameter()) {
            $this->_throw('Dozwolone jest wysyłanie do 10000 wiadomości na raz');
        }
        $this->phones[] = $to;
        $this->params[] = $params;

        return $this;
    }
    protected function _setupMultiple(&$post)
    {
        preg_match_all('#\[%(\d+)%\]#', $post['message'], $m);

        if (count($m[0])) {
            $max = 0;
            foreach ($m[1] as $k) {
                ++$max;

                if ($max > 4) {
                    $this->_throw('Dozwolone są tylko 4 argumenty');
                }

                if ($k != $max) {
                    $this->_throw('Utrzymuj kolejność parametrach, zaczynając od 1, obecna kolejność to: '.print_r($m[1], true));
                }
            }

            $params = [];

            $i = 0;
            foreach ($m[1] as $k) {
                $k = "param$k";
                $params[$k] = $i++;
            }

            if (count($params)) {  // są parametry
                $pcount = count($this->phones);
                $scount = count($params);

                foreach ($params as $p => &$ii) {
                    $tt = [];
                    $i = 0;

                    foreach ($this->params as &$par) {
                        $pc = count($par);
                        if ($pc != $scount) {
                            $this->_throw("Liczba parametrów ($pc) w zestawie nr '$i' jest inna niż liczba zanczników w templatece ($scount)");
                        }

                        if (UtilArray::isAssoc($par)) {
                            if (!isset($par[$p])) {
                                $this->_throw("Brak parametru '$p' w zestawie parametrów w elemencie $i");
                            }

                            $tt[] = $par[$p];
                        } else {
                            $tt[] = $par[$ii];
                        }

                        ++$i;
                    }

                    $c = count($tt);
                    if ($c != $pcount) {
                        $this->_throw("Liczba wartości w zestawie parametrów ($c) '$p' nie jest równa ilości odbiorców ($pcount)");
                    }

                    $params[$p] = Urlizer::unaccent(implode('|', $tt));
                }

                $post = array_merge($post, $params);
            }
        }
    }
    /**
     * array(
     *      'message' => 'message',
     *      'to'      => '123456789',
     *      '' => ...
     * ).
     *
     * @param type $sms
     */
    public function send($sms)
    {

//        die('biblioteka zablokowana - odblokowac jak bedzie potrzebne');

        $this->status = $this->result = null;

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 10); 

        if ($this->test) {
            $sms['test'] = 1;
            $sms['eco'] = 1; // co by płacić mniej za testy
        } else {
            $sms['eco'] = 0; // co by płacić mniej za testy
        }

        $post = array_merge(array(
            'username' => $this->user,
            'password' => $this->pass,
        ), $sms);

        if (empty($post['from']) && $this->from) {
            $post['from'] = $this->from;
        }

        $this->_validate($post);

        // jeśli są parametry to je pozbieram
        $this->_setupMultiple($post);

        $post['message'] = Urlizer::unaccent($post['message']);
        $post['from'] and ($post['from'] = Urlizer::unaccent($post['from']));

        if (count($this->phones)) {
            $post['to'] = $this->phones;
        }

        // ustawienie idx
        $idx = isset($post['idx']) ? $post['idx'].'_' : date('YmdHis_');
//        p($post['to']);

        if (is_array($post['to'])) {
            $i = 1;
            $list = [];

            foreach ($post['to'] as &$x) {
                $list[] = $idx.++$i;
            }

            $post['idx'] = implode('|', $list);
            $post['to'] = implode(',', $post['to']);
        } else {
            $post['idx'] = $idx.'0';
        }
//        if ($this->notify)
//            $post['notify_url'] = $this->notify;

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->url);

        if (count($post)) {
            curl_setopt($ch, CURLOPT_POST, !!count($post));
            $serializedpost = http_build_query($post);
//            var_dump($serializedpost);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $serializedpost);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//        nieginie($post);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'Authorization: Basic dGVzdDp0ZXN0b3dhbmll'
//        ));

        //execute post
        $result = null;
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        $this->result = $result;

        if (preg_match('#^OK:.+#', $result)) {
            /*
             * Limit na multiple messages to 10000 numerów
             */
            if (strpos($result, ';') !== false) {
                $list = [];
                foreach (explode(';', $result) as $t) {
                    preg_match('#OK:(\d+):([\d\.]+):(.*)#i', $t, $t);
                    if (count($t) == 4) {
                        $list[] = array(
                            'id' => $t[1],
                            'points' => $t[2],
                            'tel' => $t[3],
                        );
                    }
                }

                return $this->status = [
                    'one' => false,
                    'list' => $list,
                ];
            } else {
                return $this->status = [
                    'one' => true,
                    'id' => preg_replace('#^OK:(\d+):.*$#i', '$1', $result),
                    'points' => preg_replace('#^OK:\d+:(.*)$#i', '$1', $result),
                ];
            }
        }

        return;
    }
    public function getLastResult()
    {
        return $this->result;
    }
    public function getLastStatus()
    {
        return $this->status;
    }

    protected function _validate(&$sms)
    {
        if (empty($sms['message'])) {
            $this->_throw("Nie ustawiono pola 'message'");
        }

        if (empty($sms['from']) && !$sms['eco']) {
            $this->_throw("Nie ustawiono pola 'from', jeśli eco=0 to from musi być ustawione");
        }

        if (count($this->phones)) {
            if (!count($this->params)) {
                $this->_throw('Templatka zawiera znaczniki, ale brak jest numerów parametrów');
            }
        } else {
            if (empty($sms['to'])) {
                $this->_throw("Nie ustawiono pola 'to'");
            }
        }
    }
    protected function _throw($message, $code = 0)
    {
        throw new SmsApiException($message, $code);
    }
}
