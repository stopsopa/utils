<?php

namespace Stopsopa\UtilsBundle\Lib;

class DisposableEmails {
    static $domains = [  // lista składa się z zwykłych domen lub jeśli zaczyna się od znaku / to string traktowany jest jako wyrażenie regularne        
        'yopmail.com', // yopmail.com
        '/ee\d+\.pl/',      // koszmail.pl
        'koszmail.pl',    // koszmail.pl
        'trbvm.com',      // http://10minutemail.com/ teraz takiego używają
        'mailnesia.com',
        'flurred.com',
        'yopmail.fr',
        'yopmail.net',
        'cool.fr.nf',
        'jetable.fr.nf',
        'nospam.ze.tc',
        'nomail.xl.cx',
        'mega.zik.dj',
        'speed.1s.fr',
        'courriel.fr.nf',
        'moncourrier.fr.nf',
        'monemail.fr.nf'
    ];
    public static function isDisposable($email) {
        $domain = trim(preg_replace('#^.*?@(.*)$#', '$1', $email));
        foreach (static::$domains as &$d) {
            if ($d[0] == '/') {
                if (preg_match($d, $domain)) 
                    return true;                            
            }
            else {
                if ($domain === $d) {
                    return true;
                }                
            }
        }
        return false;
    }
}
