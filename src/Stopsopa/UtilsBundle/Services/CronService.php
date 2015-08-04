<?php

namespace Stopsopa\UtilsBundle\Services;

use Exception;

//
//
//
//$b = new BlankService('test', '#blank v', '#blank ^', '#');
//
//$b->unblank();
//niechginie($b->isBlank());

/**
 * Serwis do włączania i wyłączania zaślepki "przerwa techniczna" na front.
 * 
 * Pt\CommonBundle\Service\HtBlankService
 * 
 * w pliku posinno być:
 
 jaieś dane
 jaieś dane
 jaieś dane
 #blank v
 RewriteCond %{REQUEST_FILENAME} !-f
 RewriteCond %{REQUEST_FILENAME} !(.*\.(png|bmp|jpg|gif|js))$
 RewriteCond %{REQUEST_FILENAME} !^(admin|logout|login)
 RewriteRule ^(.*)$ /disabled.php [L] 
 #blank ^
 jaieś dane
 jaieś dane
 jaieś dane
 jaieś dane
 * 
 * 
 * następnie odpalamy: 
 * 
 * $b = new BlankService('plik.txt', '#blank v', '#blank ^', '#');
 * 
 * $b->unblank();
 * niechginie($b->isBlank());
 * 
 * ClickM\CoreBundle\Lib\CronService
 * 
 * ver 1.0 - najnonwsza wersja poprawione działanie
 */
class CronService
{
    protected $file;
    protected $start;
    protected $end;
    protected $comment;
    protected $pregcheck = '/^(\s*)([^#\s].*)$/i';
    protected $pregadd = '/^(\s*)(.*)$/i';
    protected $pregrm = '/^(\s*)#(.*)$/i';
    public function __construct($file, $start = null, $end = null, $comment = null)
    {
        $comment or ($comment = '#');
        $end     or ($end = '#blank '.'^');
        $start   or ($start = '#blank '.'v');
        $this->file = $file;
        $this->comment = $comment;
        $this->start = preg_quote($start, '/');
        $this->end = preg_quote($end, '/');
        $this->_access();
        $this->pregcheck = str_replace('#', preg_quote($this->comment, '/'), $this->pregcheck);
        $this->pregadd = str_replace('#', preg_quote($this->comment, '/'), $this->pregadd);
        $this->pregrm = str_replace('#', preg_quote($this->comment, '/'), $this->pregrm);
    }

    public function comment()
    {
        if ($parts = $this->_getParts()) {
            $d = $parts[3];

            $d = explode("\n", $d);

            foreach ($d as $key => &$data) {
                $data = rtrim($data);

                if (trim($data) && preg_match($this->pregadd, $data, $match)) {
                    $d[$key] = $this->comment.$match[1].$match[2];
                }
            }

            $parts[3] = implode("\n", $d);

            $parts = implode('', $parts);

//            niechginie($parts);
            if (!file_exists($this->file)) {
                file_put_contents($this->file, '', FILE_APPEND);
            }

            file_put_contents($this->file, $parts);
        }
    }
    public function unComment()
    {
        if ($parts = $this->_getParts()) {
            $d = $parts[3];

            $d = explode("\n", $d);

            foreach ($d as $key => &$data) {
                $data = rtrim($data);

                if (preg_match($this->pregrm, $data, $match)) {
                    $d[$key] = $match[1].$match[2];
                }
            }

            $parts[3] = implode("\n", $d);

            $parts = implode('', $parts);

//            niechginiee(file_exists($this->file));
//            niechginiee($this->file);
            if (!file_exists($this->file)) {
                file_put_contents($this->file, '', FILE_APPEND);
            }

            file_put_contents($this->file, $parts);
        }
    }
    protected function _getParts()
    {
        $this->_access();
        $content = file_get_contents($this->file);
        preg_match("/^(.*?)({$this->start})(.*?)(\s*{$this->end})(.*?)$/is", $content, $parts);

        if (isset($parts[3])) {
            unset($parts[0]);

            return $parts;
        }

        return;
    }

    /**
     * Czy blok jest zakomentowany.
     *
     * @return bool
     */
    public function isCommented()
    {
        if ($parts = $this->_getParts()) {
            $d = $parts[3];

            $d = explode("\n", $d);

            foreach ($d as $key => &$data) {
                $data = rtrim($data);

                if (preg_match($this->pregcheck, $data, $match)) {
                    return false;
                }
            }
        }

        return true;
    }
    public function _access()
    {
        $file = $this->file;

        if (!file_exists($file)) {
            throw new Exception("File '$file' not exists");
        }

        if (!is_file($file)) {
            throw new Exception("Is not file '$file'");
        }

        if (!is_writable($file)) {
            throw new Exception("File '$file' is not writeable");
        }
    }
}
