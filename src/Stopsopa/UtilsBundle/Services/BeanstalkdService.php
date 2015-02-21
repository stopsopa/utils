<?php

namespace Stopsopa\UtilsBundle\Services;

use App;
use Exception;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Lib\Json;
use Stopsopa\UtilsBundle\Services\Exceptions\BeanstalkdException;
use Symfony\Component\Process\Process;

/**
 * Dokumentacja protokołu: https://raw.githubusercontent.com/kr/beanstalkd/master/doc/protocol.txt
 * 
 * Przystosowane do pracy z netcast w wersji
 * [v1.10] - pozyskane z nc -h
 * 
 * Jest zainstalowana wersja ze strony:
http://nc110.sourceforge.net/ [^]
To czy jest rozwijana to staram się wywnioskować z tego co widzę na stronie danego oprogramowania. Nie mogę trafić na stronę openbsd-netcat, linki, które dotychczas były podawane niekoniecznie już przekierowują tam gdzie powinny. 
Na stronie http://rpm.pbone.net/index.php3/stat/4/idpl/18328005/dir/centos_6/com/nc-1.84-22.el6.x86_64.rpm.html [^] jest info o "obsoletności" tego pakietu.
Poza tym wszystkie forki netcata są tak intensywnie rozwijane, że jest to kwestia czy w tym dziesięcioleciu były commity czy nie:/
 * 
 * 
 * Stopsopa\UtilsBundle\Services\BeanstalkdService
 */
class BeanstalkdService {
    protected $host;
    protected $port;
    protected $tube;
    protected $quitmode;
    public function __construct($tube) {
        $c              = App::getConfig('beanstalkd');
        $this->host     = $c['host'];
        $this->port     = $c['port'];
        $this->quitmode = $c['quitmode'];
        $this->tube     = $tube;
    }
    /**
        [name] => default
        [current-jobs-urgent] => 2
        [current-jobs-ready] => 32
        [current-jobs-reserved] => 0
        [current-jobs-delayed] => 0
        [current-jobs-buried] => 0
        [total-jobs] => 0
        [current-using] => 1
        [current-watching] => 1
        [current-waiting] => 0
        [cmd-delete] => 0
        [cmd-pause-tube] => 0
        [pause] => 0
        [pause-time-left] => 0
     */
    public function getStatTube($key, $tube = null) {
        $d = $this->getStatsTube($tube);
        
        if (array_key_exists($key, $d))
            return $d[$key];
        
        throw new Exception("No stat found by name: '$key'");
    }
    public function getStatsTube($tube = null, $throw = true) {
        $tube or ($tube = $this->tube);
        
        $cmd = "stats-tube $tube";
        
        $data = $this->_netcat($cmd);

        try {            
            if (trim($data) === 'UNKNOWN_COMMAND')
                throw new Exception("Netcat: UNKNOWN_COMMAND, cmd: '$cmd'");

            if (trim($data) === 'NOT_FOUND')
                throw new Exception("Netcat: NOT_FOUND, cmd: '$cmd'");
        }
        catch (Exception $ex) {
            if (!$throw) 
                return $data;
            throw $ex;
        }
        
        preg_match_all('#\n([^\:\n]+)\:\s*([^\n]+)#', $data, $match);
        
        $list = array();
        if (isset($match[1]) && count($match[1])) {
            foreach ($match[1] as $key => $name) {
                $list[$name] = $match[2][$key];
            }
        }
        return $list;
    }
    protected function _netcat($cmd) {
        $cmd = str_replace('"', '\\"', $cmd);
        $host = $this->host;
        $port = $this->port;
        $quit = $this->quitmode ? ' -q 1' : '';
        $command = "echo -e \"$cmd\r\n\" | nc$quit $host $port";
        
//        $command = str_replace("\r\n", "\\r\\n", $command);
//        throw new Exception($command);
        
        $p = new Process($command);
        $p->setTimeout(null);

        try {
            $p->run();            
        } catch (ProcessTimedOutException $ex) {
            throw new Exception("Nie udało się wykonać komendy '$command' przez netcat, błąd: {$ex->getMessage()}");            
        }

        if (!$p->isSuccessful()) 
            throw new Exception('Netcat exit with error');  
        
        return $p->getOutput();
    }
    protected function _parseReservedAnswer($data) {
        try {
            if (strpos($data, 'RESERVED') !== false) {
                preg_match('#WATCHING \d+.*?WATCHING \d+.*?RESERVED (\d+) \d+(.*)#s', $data, $m);
                $data = Json::decode(trim($m[2]));
                
                if (is_null($data)) 
                    throw new BeanstalkdException("Empty data from reserve", BeanstalkdException::EMPTYDATA_ERROR);
                
                return array(
                    'id'   => $m[1],
                    'data' => $data
                );
            }
            throw new BeanstalkdException("Parse error, data: '$data'", BeanstalkdException::PARSE_ERROR);           
        } catch (Exception $ex) {
            
            if ($ex instanceof BeanstalkdException) {
                $this->delete($m[1]);                                
            }
            else 
                throw $ex;                            
        }
    }
    

    public function put($data, $ttr = 0, $delay = 0, $pri = 9999, $tube = null) {
        $data = Json::encode($data);
        $len  = strlen($data);        
        // tu jest niezły mindfuck
        $data = preg_replace('#\\\\(u\d{4})#', '\\\\\\\\\\\\\\\\$1', $data);
        $tube or ($tube = $this->tube);
        $cmd  = "use $tube\r\nput $pri $delay $ttr $len\r\n$data";
        $data = $this->_netcat($cmd);                
        
        if (strpos($data, 'INSERTED') === false)                        
            throw new BeanstalkdException("Benstalkd put error, wrong response: '$data', command: '$cmd'", BeanstalkdException::PUT_ERROR);     
        
        return $this;
    }
    /**
     * Zabranie ostatniego job z tuby sprawia że tuba znika
     * @param type $tube
     * @return type
     * @throws BeanstalkdException
     */
    public function reserve($tube = null) {
        $tube or ($tube = $this->tube);
        $cmd = "watch $tube\r\nignore default\r\nreserve";
        $data = $this->_netcat($cmd);
        
        if (strpos($data, 'TIMED_OUT') !== false) 
            throw new BeanstalkdException("Wystąpił błąd podczas reserwacji, '$data'", BeanstalkdException::EMPTYTUBE_ERROR);
        
        if (strpos($data, 'RESERVED') === false) 
            throw new BeanstalkdException("Wystąpił błąd podczas reserwacji, '$data'", BeanstalkdException::RESERVE_ERROR);

        return $this->_parseReservedAnswer($data); 
    }
    public function delete($id) {
        $tid = (int)$id;
        
        if ($tid < 1)  
            throw new BeanstalkdException("Benstalkd delete error, wrong id: '".print_r($id, true)."'", BeanstalkdException::DELETE_ERROR);
        
        $cmd = "delete $tid";
        $data = $this->_netcat($cmd);
        
        if (strpos($data ,'DELETED') === false) 
            throw new BeanstalkdException("Benstalkd delete error, wrong response: '".trim(print_r($data, true))."'", BeanstalkdException::DELETE_ERROR);
        
        return $this;
    }
    public function bury($id) {
        $tid = (int)$id;
        
        if ($tid < 1)  
            throw new BeanstalkdException("Benstalkd bury error, wrong id: '".print_r($id, true)."'", BeanstalkdException::BURY_ERROR);
        
        $cmd = "bury $tid 0";
        $data = $this->_netcat($cmd);
        
        if (strpos($data ,'BURIED') === false) 
            throw new BeanstalkdException("Benstalkd bury error, wrong response: '".trim(print_r($data, true))."'", BeanstalkdException::BURY_ERROR);
        
        return $this;
    }
    public function listTubes() {
        $cmd = 'list-tubes';
        $data = $this->_netcat($cmd);
        
        preg_match_all('#- ([^\s]+)\n#', $data, $m);
        
        if (isset($m[1])) 
            return $m[1];
        
        return array();
    }
}