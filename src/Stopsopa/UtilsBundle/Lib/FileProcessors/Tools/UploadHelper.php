<?php

namespace Stopsopa\UtilsBundle\Lib\FileProcessors\Tools;

use Stopsopa\UtilsBundle\Lib\Standalone\UtilFilesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use DateTime;

class UploadHelper
{
    /**
     * @var BaseRequest
     */
    protected $request;
    /**
     * @var AbstractFileProcessor[]
     */
    protected $processors;

    protected $response;

    public function __construct(BaseRequest $request)
    {
        $this->request = $request;
        $this->processors = array();
    }
    public function addProcessor(AbstractFileProcessor $processor)
    {
        $this->processors[] = $processor;
    }
    public function countFiles()
    {
        $paths = $this->_extractFilePaths($this->request->files->all());
//        nieginie($this->request->files->all(), 2);
//        nieginie(count($paths));
//        niechginie($paths, 2);
        return count($paths);
    }

    /**
     * $d = UtilFormAccessor::setValue($form, 'user[comments][1][path]', 'test');.
     */
    public function handle()
    {
        $paths = $this->_extractFilePaths($this->request->files->all());

        $response = array();

        foreach ($paths as $path => &$file) {
            $processor = $this->_getProcessor($path);
            $config = $processor->getConfig();

            $prefix = preg_replace('#^(.*?)\.[^\.]+$#', '$1', $path);

//            if (strpos($prefix, '.') === false) {
//                $form = $this->form;
//            }
//            else {

//                $form = UtilFormAccessor::getForm($this->form, $prefix);
//
//                niechginie(array(
//                    $prefix,
//                    $this->form,
//                    $form,
//                    $form->getData()
//                ));
//
////                niechginie($form);
//            }

            $result = new UploadResult($prefix.'.'.$config['field'], str_replace('.', '_', $path), $this->request);

            $response[] = $result;

            $processor->handle($file, $result);
        }

        $return = array();
        foreach ($response as $res) {
            /* @var $res UploadResult */
            $return[] = $res->getResult();
        }

        return array(
            'files' => $return,
        );
    }
    public function move()
    {
        $paths = $this->_extractHiddenPaths($this->request->request->all());

        foreach ($paths as $path => $file) {
            if (trim($file)) {
                $processor = $this->_getProcessorByPath($path);

                $config = $processor->getConfig();

                $tmp = $config['web'].$config['dirtmp'].$file;

                if (file_exists($tmp)) {
                    UtilFilesystem::rename($tmp, $config['web'].$config['dir'].$file);
                    UtilFilesystem::removeEmptyDirsToPath(dirname($tmp), $config['web'].$config['dirtmp']);
                }
            }
        }
    }

    public function getData()
    {
        $list = array();

        foreach ($this->response as $response) {
            /* @var $response UploadResult */
            $list[] = $response->getPath();
        }

        return $list;
    }

    protected function _extractFilePaths($paths)
    {
        $list = array();

        $this->_extractOneFile($paths, '', $list);

        return $list;
    }

    protected function _extractOneFile(&$tree, $prefix, &$list)
    {
        foreach ($tree as $key => &$data) {
            if ($prefix) {
                $key = $prefix.'.'.$key;
            }

            if ($data instanceof UploadedFile) {
                $list[$key] = $data;
                continue;
            }

            if (is_array($data)) {
                $this->_extractOneFile($data, $key, $list);
            }
        }
    }

    protected function _extractHiddenPaths($paths)
    {
        $list = array();

        $this->_extractOneHiddenPath($paths, '', $list);

        return $list;
    }
    protected function _extractOneHiddenPath(&$tree, $prefix, &$list)
    {
        foreach ($tree as $key => &$data) {
            if ($prefix) {
                $tmpkey = $prefix.'.'.$key;
            } else {
                $tmpkey = $key;
            }

            $processor = $this->_getProcessorByPath($tmpkey);

            if ($processor && $key === $processor->_getConfig('field')) {
                $list[$tmpkey] = $data;
                continue;
            }

            if (is_array($data)) {
                $this->_extractOneHiddenPath($data, $tmpkey, $list);
            }
        }
    }
    public static $processorcache;
    /**
     * @param string $path
     *
     * @return AbstractFileProcessor
     */
    protected function _getProcessor($path)
    {
        if (!$path) {
            return;
        }

        if (!is_array(static::$processorcache)) {
            static::$processorcache = array();
        }

        if (array_key_exists($path, static::$processorcache)) {
            return static::$processorcache[$path];
        }

        foreach ($this->processors as &$processor) {

            /* @var $processor AbstractFileProcessor */
            $config = $processor->getConfig();

            if (preg_match($config['file'], $path)) {
                static::$processorcache[$path] = $processor;

                return $processor;
            }
        }
    }
    public static $processorcachepath;
    /**
     * @param string $path
     *
     * @return AbstractFileProcessor
     */
    protected function _getProcessorByPath($path)
    {
        if (!$path) {
            return;
        }

        if (!is_array(static::$processorcachepath)) {
            static::$processorcachepath = array();
        }

        if (array_key_exists($path, static::$processorcachepath)) {
            return static::$processorcachepath[$path];
        }

        foreach ($this->processors as &$processor) {

            /* @var $processor AbstractFileProcessor */
            $match = $processor->_getConfig('fieldmatch');

            if (preg_match($match, $path)) {
                static::$processorcachepath[$path] = $processor;

                return $processor;
            }
        }
    }
    /**
     *
    // do obadania vvv
     * Http caching
    // https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching?hl=en
    // http://www.mobify.com/blog/beginners-guide-to-http-cache-headers/
              https://js-agent.newrelic.com/nr-632.min.js wzorowy link zobacz nagłóki zwracane podczas response łąduje się na stronie talentdays
    //        $mod    = new DateTime(date("c", filemtime($file)));
    //        $mod->sub(new DateInterval("P"));
    //        header('Cache-Control: public');
    //        header('Cache-Control: Cache-Control: no-transform,public,max-age=300,s-maxage=900');
    // do obadania ^^^
     *
     * // $_SERVER[HTTP_IF_MODIFIED_SINCE] => Tue, 04 Aug 2015 15:34:00 GMT
     * aby zmienić czas modyfikacji pliku wystarczy echo 'console.log('go');' > test.js && date
     *
     * po stronie php:

    $modified = HttpIfModifiedSince::isModified($file, @$_SERVER['HTTP_IF_MODIFIED_SINCE']);

    if ($modified) {
    // http://php.net/manual/en/class.datetime.php
    header('Last-Modified: '.  gmdate("D, d M Y H:i:s T", filemtime($file))  ); // tak lepiej Last-Modified: Tue, 22 Sep 2015 21:15:31 GMT
    //        header('Last-Modified: '.  date("D, d M Y H:i:s T", filemtime($file))  ); //            Last-Modified: Tue, 22 Sep 2015 23:16:05 CEST
    readfile($file);
    }
    else {
    header('HTTP/1.0 304 Not Modified');
    }
    die();
     *
     *
     * po stronie js:
     *
    "Tue, 22 Sep 2015 20:15:36 GMT"   http://stackoverflow.com/a/13594069/1338731    new - RFC 1123, and old RFC 822
    (new Date()).toUTCString()
    var t = new Date ()
    console.log(t.toUTCString())
    d1.setMinutes ( t.getMinutes() + 30 );
    console.log(t.toUTCString())
     * @param $file
     * @param null $lastmodifiedheader
     * @return bool
     * @throws Exception
     */
    public static function isModified($file, $lastmodifiedheader = null)
    {
        if (!$lastmodifiedheader) {
            return true;
        }

        $date   = new DateTime(date("c", strtotime($lastmodifiedheader)));

        $start  = new DateTime('1970-01-01');

        if ($date > $start) { // jest sensowna data a nie 1970-01-01

            if (!file_exists($file)) {
                throw new Exception("File '$file' not exists");
            }

            if (!is_file($file)) {
                throw new Exception("'$file' is not file");
            }

            if (!is_readable($file)) {
                throw new Exception("File '$file' not readable");
            }

            $mod    = new DateTime(date("c", filemtime($file)));

//                niechginie(array( // debug
//                    '$date' => $date->format('Y-m-d H:i:s'),
//                    '$mod' => $mod->format('Y-m-d H:i:s'),
//                    '$mod > $date' => ($mod > $date) ? 'true' : 'false'
//                ), 2);

            return $mod > $date;
        }

        throw new Exception('Wrong If-Modified-Since header ('.$lastmodifiedheader.'), should be example: If-Modified-Since: Tue, 22 Sep 2015 20:26:03 GMT');
    }

    /**
     * $modified = HttpIfModifiedSince::eTagChanged($file, @$_SERVER['HTTP_IF_NONE_MATCH']);
     */
    public static function eTagChanged($file, $etag = null) {

        $etag = trim($etag, '" ');

        if (!$etag) {
            return true;
        }

        if (!file_exists($file)) {
            throw new Exception("File '$file' not exists");
        }

        if (!is_file($file)) {
            throw new Exception("'$file' is not file");
        }

        if (!is_readable($file)) {
            throw new Exception("File '$file' not readable");
        }

        return hash_file('crc32', $file) !== $etag;
    }
    /**
     * $modified = HttpIfModifiedSince::fileChanged($file, @$_SERVER['HTTP_IF_NONE_MATCH'], @$_SERVER['HTTP_IF_MODIFIED_SINCE']);
     */
    public static function fileChanged($file, $etag = null, $lastmodifiedheader = null) {

        if ($etag) {
            return HttpIfModifiedSince::eTagChanged($file, $etag);
        }

        if ($lastmodifiedheader) {
            return HttpIfModifiedSince::isModified($file, $lastmodifiedheader);
        }

        return true;
    }

    public function delete($entity)
    {
        foreach ($this->processors as $processor) {
            $config = $processor->getConfig();

            if ($entity instanceof $config['class']) {
                return $processor->delete($entity);
            }
        }
    }
}
