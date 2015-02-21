<?php

namespace Stopsopa\UtilsBundle\Services;

use App;
use Exception;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Lib\Json;
use Stopsopa\UtilsBundle\Services\Exceptions\BeanstalkdException;
use Symfony\Component\Process\Process;

/**
 * Stopsopa\UtilsBundle\Services\SupervisordService
 */
class SupervisordService {
    protected $tube;
    public function __construct($tube) {
        $this->tube = $tube;
    }
    
    public function on() {
        return $this->_changeState('start');        
    }
    public function off() {
        return $this->_changeState('stop');
    }
    public function switchState() {
        return $this->isOn() ? $this->off() : $this->on();
    }

    protected function _changeState($state) {
        $command = "sudo supervisorctl $state {$this->tube}";
        
        $p = new Process($command);
        $p->setTimeout(null);

        try {
            $p->run();            
        } catch (ProcessTimedOutException $ex) {
            throw new Exception("Nie udało się wykonać komendy '$command' przez netcat, błąd: {$ex->getMessage()}");            
        }

        if (!$p->isSuccessful()) 
            throw new Exception('Netcat exit with error');  
        
        return $this;
    }

    public function isOn() {
        $command = "sudo supervisorctl status";
        $p = new Process($command);
        $p->setTimeout(null);

        try {
            $p->run();            
        } catch (ProcessTimedOutException $ex) {
            throw new Exception("Nie udało się wykonać komendy '$command' przez netcat, błąd: {$ex->getMessage()}");            
        }

        if (!$p->isSuccessful()) 
            throw new Exception('Netcat exit with error');  
        
        $data = $p->getOutput();
        
        preg_match("#{$this->tube}[^\\n]+#si", $data, $m);
        if (isset($m[0])) {
            if ( strpos($m[0], 'RUNNING') !== false) {
                return true;
            }
        }
        return false;
    }
}