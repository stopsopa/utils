<?php

namespace Stopsopa\UtilsBundle\Services;

use App;
use Swift_Message;
/**
 * Stopsopa\UtilsBundle\Services\RaportService
 */
class RaportService {
    protected $emails;
    public function __construct($emails) {
        $this->emails = $emails;
    }
    public function raport() {
        $key    = 'crontime';
        $limit = 8;
        $cache  = App::getCache();
        $worker = App::getServiceWorker();
        
        $ltime = $cache->get($key);   // poprzednie badanie
//        niechginie($ltime);
        $ntime = date('Y-m-d H:i:s'); // teraz
        $ltime or ($ltime = $ntime);
        $cache->set($key, $ntime);        
//        nieginie($ltime);
//        nieginie($worker->countErrorFromSince($ltime));
//        niechginie($worker->getLastErrors($ltime));
        
        if ($num = $worker->countErrorFromSince($ltime)) {
            $body = App::template('cronraport', array(
                'list'  => $worker->getLastErrors($ltime, $limit),
                'limit' => $limit,
                'ltime' => $ltime,
                'num'   => $num,
                'host'  => getHost(),
                'panel' => App::generate('admin', array(), $referenceType = true)
            ));

            $message = Swift_Message::newInstance()
                ->setSubject('webservice ['.getHost().'] - error')
                ->setFrom('noreplay@pcube.com', 'webservice') 
                ->setTo($this->emails)
                ->setBody($body, 'text/html')
                ->addPart(strip_tags($body), 'text/plain')
            ;
            App::getServiceMailer()->send($message);              
        }
    }
}