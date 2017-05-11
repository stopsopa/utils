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
 * Stopsopa\UtilsBundle\Services\CompressInitService.
 */
class CompressInitService
{
    public function dump($version)
    {
    }
    public function getApiList()
    {
        $root = App::getRoot();
        $dir = $root.'/src/public';
        $list = array();

        $finder = new Finder();
        foreach ($finder->in($dir)->files()->name('init.js') as $file) {
            /* @var $file SplFileInfo */
//            $tmp[$file->getRealPath()] = $file->getSize();
            $list[] = preg_replace('#^.*?\/(\d+)\/js\/init\.js$#i', '$1', $file->getRealPath());
        }

        asort($list);

        return $list;
    }
    public function getInitJsContent()
    {
        $list = array();

        $domain = preg_quote(App::getConfig('system.host'), '#');

        foreach ($this->getApiList() as $ver) {
            $url = App::generate('apiinit', array(
                'version' => $ver,
            ), true);

            $ch = curl_init($url);                    
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 10); 
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $return = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($status !== 200) {
                throw new Exception("Curl to '$url' return status '$status' http code. Curl error description: '".curl_error($ch)."'");
            }

            if (!$return) {
                throw new Exception("Curl to '$url' return '".print_r($return, true)."'");
            }

            $list[$ver] = array(
                'ver' => $ver,
                'url' => $url,
                'path' => preg_replace("#^.*$domain(.*)$#", '$1', $url),
                'content' => $return,
            );
        }

        return $list;
    }
    public function _createFileName()
    {
        do {
            $file = '/output/'.substr(md5(uniqid()), 0, 5).'.js';
        } while (file_exists($file));

        return $file;
    }

    public function compressAll()
    {
        foreach ($this->getInitJsContent() as $name => $data) {
            //            $file = $this->_createFileName();            
            $file = tempnam(realpath(sys_get_temp_dir()), 'yui-compress-');
            file_put_contents($file, $data['content']);

            $cmd = "php app/console yui --format=js --input=$file --output=$file";

            $p = new Process($cmd);
            $p->setTimeout(null);

            try {
                $p->run();
            } catch (ProcessTimedOutException $ex) {
                throw $ex; // a nie bosługuję zostawię taj jak jest
            }

            if (!$p->isSuccessful()) {
                throw new Exception('Exit with error');
            }

            $this->transport($file, $data);

            file_exists($file) and unlink($file);
        }
    }
    public function transport($file, $data)
    {
        $c = App::getConfig('project.cdnwebdir');

        $webdir = $c.dirname($data['path']);

        UtilFilesystem::mkDir($webdir, true);

        $target = $c.$data['path'];

        copy($file, $target);

        if (file_exists($target)) {
            echo "Plik $target został utworzony".PHP_EOL;
        } else {
            throw new Exception("Nie utworzono pliku $target");
        }
    }
}
