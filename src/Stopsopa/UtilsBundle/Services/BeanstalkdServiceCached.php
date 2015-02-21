<?php

namespace Stopsopa\UtilsBundle\Services;

use App;
use Exception;
use Lib\UtilFilesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * Ta warstwa tej usługi ma na celu wyeliminowanie sytuacji w której job jest wrzucany do csv dwa razy z rzędu,
 * oraz co za tym idzie żeby nie dopuścić do sytuacji ze z powodu jakiegoś błędu 
 * jeden rekord trafi do hurtowni milion razy, wszystko ma się zatrzymać przy pierwszym błędzie
 * Stopsopa\UtilsBundle\Services\BeanstalkdServiceCached
 */
class BeanstalkdServiceCached extends BeanstalkdService {
    protected $key = 'beanstalknotdeletedtask';
    public function reserve($tube = null, $lock = true) {
        $data = parent::reserve($tube); 
        if ($lock) {
            $cache = App::getCache();

            if ($cache->get($this->key) === $data['id']) 
                throw new Exception("Job by id '{$data['id']}' is reserved second time. Now to reset this error you need to run App::getServiceBeanstalkd()->resetCache()");

            $cache->set($this->key, $data['id']);            
        }

        return $data;  
    }
    public function resetCache() {
        $cache = App::getCache();
        $cache->set($this->key, null);
        return $this;
    }
}