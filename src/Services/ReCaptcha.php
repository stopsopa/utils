<?php

namespace CoreBundle\Libs;

use CoreBundle\Libs\Request;
use Exception;

/**
 * @package CoreBundle\Libs\ReCaptcha
 

    recaptcha:
        class: CoreBundle\Libs\ReCaptcha
        calls:
            -
                method: setSecretKey
                arguments:
                    - "%recaptcha_secretkey%"
                    
                    
    recaptcha_secretkey: ...
    recaptcha_public: ...                 
 */
class ReCaptcha {
    const SERVICE = 'recaptcha';
    protected $secretkey;
    protected $ip;
    public function setIp($ip) {
        $this->ip = $ip;
    }
    public function setSecretKey($secretkey) {
        $this->secretkey = $secretkey;
    }
    public function checkRequest(Request $request) {

        $secret = $request->json->get('solution');

        return $this->check($secret);
    }
    public function check($solution, $secretkey = null, $ip = null) {

        $secret     = $secretkey ?: $this->secretkey;

        $ip         = $ip ?: $this->ip;

        if (!$secret) {

            throw new \Exception("Provide first secret key");
        }

        $solution = trim($solution);

        if (!$solution) {
            return array(
                'success' => false,
                'error-codes' => array(
                    'empty-input-response'
                )
            );
        }

        $ch = curl_init();

        $data = array(
            'secret' => $secret,
            'response' => $solution
        );

        if ($ip) {
            $data['remoteip'] = $ip;
        }

        curl_setopt_array($ch, array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
//            CURLOPT_URL => $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/test/rc/html.php?mirror",
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true
        ));

        $result = null;

        $result = curl_exec($ch);

        curl_close($ch);

        $json = json_decode($result, true);

        if (is_array($json) && count($json)) {

            if (!empty($json['success']) && $json['success'] === true) {

                return true;
            }

            return $json;
        }

        throw new \Exception($result);
    }
}
