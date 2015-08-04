<?php

namespace Stopsopa\UtilsBundle\Services;

use App;
use Exception;
use Lib\AbstractException;
use Lib\UtilFilesystem;
use PDO;
use Stopsopa\UtilsBundle\Services\Exceptions\BeanstalkdException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * Stopsopa\UtilsBundle\Services\WorkerService.
 */
class WorkerService
{
    const MAXLINESINFILE = 1000;
    public function consume()
    {
        AbstractException::setErrorHandler();

        $s = App::getServiceBeanstalkd();

        $i = 50;

        while ($i--) {
            try {
                $j = $s->reserve();

                $data = $j['data'];

                if (isset($data['mode']) && $data['mode'] === 'checkfile') {
                    App::getServiceWorker()->checkFiles();
                } else { // default api call, tj: $data['mode'] === 'apicall'
                    if (empty($data['ver'])) {
                        throw new Exception("Parametr 'ver' nie jest obecny w danych wejściowych: '".trim(print_r($j['data'], true))."'");
                    }

                    if (empty($data['method'])) {
                        throw new Exception("Parametr 'method' nie jest obecny w danych wejściowych: '".trim(print_r($j['data'], true))."'");
                    }

                    if (empty($data['data'])) {
                        throw new Exception("Parametr 'data' nie jest obecny w danych wejściowych: '".trim(print_r($j['data'], true))."'");
                    }

                    App::getApi($data['ver'])->{$data['method']}($data['data']);
                }

                $s->delete($j['id']);
            } catch (BeanstalkdException $ex) {
                if (in_array($ex->getCode(), array(BeanstalkdException::RESERVE_ERROR, BeanstalkdException::EMPTYTUBE_ERROR))) {
                    echo "\n-w3-";
                    sleep(2);
                } else {
                    throw $ex;
                }
            } catch (Exception $ex) {
                $s->delete($j['id']);
                App::getServiceLog()->log($ex->getMessage(), $j);
//                niechginiee($ex->getMessage());
//                niechginie($ex->getTraceAsString());
            }
        }
    }
    /**
     * @param bool $onlyregister - true- rejestruje w beanstalk że trzeba jak najszybciej odpalić ten proces, false, wykonuje logikę samego procesu
     *
     * @throws Exception
     */
    public function checkFiles($onlyregister = false)
    {
        if ($onlyregister) {
            $s = App::getServiceBeanstalkd();
            try {
                $j = $s->reserve(null, false);

                $data = $j['data'];

                if (isset($data['mode']) && $data['mode'] === 'checkfile') {
                    echo 'esc1';

                    return;
                }
            } catch (BeanstalkdException $ex) {
                if ($ex->getCode() !== BeanstalkdException::EMPTYTUBE_ERROR) {
                    throw $ex;
                }
            }

            $s->put(array(
                'mode' => 'checkfile',
            ), $ttr = 0, $delay = 0, $pri = 1);

            return;
        }

        foreach (array(

            App::getConfig('clxsource.storageidcsv') => App::getConfig('clxsource.etltmpidcsv'),
            App::getConfig('clxsource.storagewarmcsv') => App::getConfig('clxsource.etltmpwarmcsv'),
            App::getConfig('clxsource.storagepbindjson') => App::getConfig('clxsource.etlpbindjson'),

        ) as $file => $target) {
            UtilFilesystem::checkIfFileExistOrICanCreate($file, true);
            $p = new Process("wc -l $file | awk '{print $1}'");
            $sek = 5;
            $p->setTimeout($sek);

            try {
                $p->run();
            } catch (ProcessTimedOutException $ex) {
                throw new Exception("Zliczanie linii w pliku '$file' przekroczyło dozwolony czas $sek sekund");
            }

            if (!$p->isSuccessful()) {
                throw new Exception("Coś poszło nie tak podczas zliczania linii w pliku '$file'");
            }

            if ((int) $p->getOutput() > static::MAXLINESINFILE) {
                $target = str_replace('*', date('Y_m_d_H_i_s'), $target);
                $dir = dirname($target);
                if (UtilFilesystem::checkDir($dir, true)) {
                    echo "\nPrzenoszę plik $file";
                    rename($file, $target);
                    touch($file);
                }
            } else {
                $file = pathinfo($file, PATHINFO_BASENAME);
                echo "\n'$file' don't move";
            }
        }
    }
    public function getLastErrors($time, $num = 3)
    {
        $num = (int) $num;
        $sql = "SELECT * FROM Logs WHERE time > '$time' ORDER BY time DESC LIMIT $num";
        $stmt = App::getPdo('log')->query($sql);

        $list = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[] = $row;
        }

        return $list;
    }
    public function countErrorFromSince($time)
    {
        $sql = "SELECT count(*) c FROM Logs WHERE time > '$time'";
        $stmt = App::getPdo('log')->query($sql);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return (int) $row['c'];
        }

        return 0;
    }
}
