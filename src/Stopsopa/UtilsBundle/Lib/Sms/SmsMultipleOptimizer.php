<?php

namespace Stopsopa\UtilsBundle\Lib\Sms;
use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Stopsopa\UtilsBundle\Lib\Json\Json;
use DateTime;


class SmsMultipleOptimizer extends Sms {
    protected $veryfirst = [];
    const LOCKFILE = '/var/sms_lock.log';
    const LOCKLIMIT = 100; // wyrażane w
    const LOCKEXPIRE = 86400; // wyrażane w sekundach (60*60*24 = 1 dzień)
    protected $lockfile;
    public function __construct($url, $user, $pass, $veryfirst) {
        $this->lockfile = App::getRootDir().static::LOCKFILE;

        if (!file_exists($this->lockfile))
            touch($this->lockfile);

        if (!file_exists($this->lockfile))
            $this->_throw("Can't create file {$this->lockfile}");

        if (!is_writable($this->lockfile))
            $this->_throw("File {$this->lockfile} is not writtable");

        $this->veryfirst = $veryfirst;
        
        parent::__construct($url, $user, $pass);
    }
    protected function checkLock($idx) {
        $list = Json::decode(file_get_contents($this->lockfile)) ?: [];

        $s = static::LOCKEXPIRE;

        $expire = new DateTime("-$s seconds");

        if (count($list)) {
            foreach ($list as $_idx => $time) {
                $time = new DateTime($time);

                if ($time < $expire)
                    unset($list[$_idx]);
            }

            uasort($list, function ($a, $b) {
                return preg_replace('#[^\d]#', '', $a) < preg_replace('#[^\d]#', '', $b);
            });

            $list = array_slice($list, 0, static::LOCKLIMIT);

            file_put_contents($this->lockfile, Json::encode($list));
        }

        if (isset($list[$idx]))
            $this->_throw("checkLock by key '$idx', go vim {$this->lockfile}", SmsApiException::LOCKERROR);

        $list[$idx] = date('Y-m-d H:i:s');

        file_put_contents($this->lockfile, Json::encode($list));
    }

    public function sendMultipleMessages($message, $params, $idx = null) {

        if ($idx)
            $this->checkLock($idx);

        $countbefore        = count($params);

        $params             = $this->filterWrongNumbers($params);

        $params             = $this->fixOrderOfMessages($params);

        $ret                = parent::sendMultipleMessages($message, $params, $idx);

        $ret['countbefore'] = $countbefore;
        $ret['countafter']  = count($params);
        return $ret;
    }
    /**
     * Odfiltrowuje numery prawdopodobnie nieprawidłowe
     * na przykład jak wystapi w numerze 8888 i tak dla cyfr od 0 do 9
     * - lub dwa razy występuje w numerze 123
     * - lub występuje raz 123 razem z 678
     * - także jeśli jest mumer 9-cio cyfrowy a zaczyna się od 0
     * - jeśli numer ma mniej niż 9 cyfr
     *
     * @param type $params
     * @return array
     *
     * Wynik redukcji poniższą funkcją:
     * zapytanie:
     *
select u.phone
from users u
where u.phone is not null
order by u.phone
     *
     * liczba numerów przed odfiltrowaniem                  35628
     * liczba odfiltrowoanych numerów po użyciu tej metody  35088
     *
     * 0.07gr (dla sms pro) * 540  = 37,8 PLN
     */
    public function filterWrongNumbers($params) {
        $list = [];
        foreach ($params as $phone => &$param) {

            if (!is_string($phone) && !is_integer($phone))
                continue;

            $phone = trim($phone);

            if (strlen($phone) < 9)
                continue;

            if (!strlen($phone))
                continue;

            if (strlen($phone) === 9 && strpos($phone, '0') === 0)
                continue;

            $continue   = false;
            $offset     = false;
            for ( $i = 0 ; $i < 10 ; ++$i ) {
                $tmp = false;
                if ($i < 8) {
                    $tmp            =  $i;
                    $tmp            .= $i+1;
                    $tmp            .= $i+2;
                }

                $tmp2           = str_repeat($i, 3);

                if ($tmp && $offset === false)
                    $offset = strpos($phone, $tmp);

                if ($offset === false)
                    $offset = strpos($phone, $tmp2);

                if ($offset !== false) {
                    if ($tmp && strpos($phone, $tmp, $offset+3) !== false) {
                        $continue = true;
                        break;
                    }

                    if (strpos($phone, $tmp2, $offset+3) !== false) {
                        $continue = true;
                        break;
                    }
                }
            }

            if ($continue)
                continue;

            for ( $i = 0 ; $i < 10 ; ++$i ) {
                if (strpos($phone, str_repeat($i, 4)) !== false) {
                    $continue = true;
                    break;
                }
            }

            if ($continue)
                continue;

            $list[$phone] = $param;
        }

        return $list;
    }
    public function fixOrderOfMessages($params) {

        if (!count($this->veryfirst))
            return $params;

        $list = array();

        foreach ($params as $phone => &$data) {
            if (in_array($phone, $this->veryfirst)) {
                $list[$phone] = $data;
            }
        }

        foreach ($params as $phone => &$data) {
            if (!in_array($phone, $this->veryfirst)) {
                $list[$phone] = $data;
            }
        }

        return $list;
    }
}