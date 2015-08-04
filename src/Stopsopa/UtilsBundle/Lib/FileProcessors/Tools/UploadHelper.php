<?php

namespace Stopsopa\UtilsBundle\Lib\FileProcessors\Tools;

use Stopsopa\UtilsBundle\Lib\Standalone\UtilFilesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as BaseRequest;

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
