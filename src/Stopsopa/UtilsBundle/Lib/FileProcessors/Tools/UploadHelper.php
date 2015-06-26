<?php


namespace Stopsopa\UtilsBundle\Lib\FileProcessors\Tools;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Stopsopa\UtilsBundle\Lib\FileProcessors\Tools\AbstractFileProcessor;
use Stopsopa\UtilsBundle\Lib\UtilFormAccessor;

class UploadHelper {
    /**
     * @var Form
     */
    protected $form;
    /**
     * @var BaseRequest
     */
    protected $request;
    /**
     * @var AbstractFileProcessor[]
     */
    protected $processors;

    protected $response;
    public function __construct(Form $form, BaseRequest $request) {
        $this->form         = $form;
        $this->request      = $request;
        $this->processors   = array();
    }
    public function addProcessor(AbstractFileProcessor $processor) {
        $this->processors[] = $processor;
    }



    /**
     * $d = UtilFormAccessor::setValue($form, 'user[comments][1][path]', 'test');
     */
    public function handle() {
        $paths = $this->_extractPath($this->request->files->all());

        $response = array();

        foreach ($paths as $path => &$file) {
            $processor = $this->_getProcessor($path);
            $config    = $processor->getConfig();

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

            $result = new UploadResult($prefix.'.'.$config['field'], str_replace('.', '_', $prefix), $this->form);

            $response[] = $result;

            $processor->handle($file, $this->form, $result);
        }

        $this->response = $response;
    }
    public function getData() {
        $list = array();

        foreach ($this->response as $response) {
            /* @var $response UploadResult */
            $list[] = $response->getPath();
        }

        return $list;
    }

    protected function _extractPath($paths) {
        $list = array();

        $this->_extractOne($paths, '', $list);

        return $list;
    }
    protected function _extractOne($tree, $prefix, &$list) {
        foreach ($tree as $key => &$data) {

            if ($prefix) {
                $key = $prefix.'.'.$key;
            }

            if ($data instanceof UploadedFile) {
                $list[$key] = $data;
                continue;
            }

            if (is_array($data)) {
                $this->_extractOne($data, $key, $list);
            }
        }
    }
    /**
     *
     * @param string $path
     * @return AbstractFileProcessor
     */
    protected function _getProcessor($path) {
        foreach ($this->processors as &$processor) {

            /* @var $processor AbstractFileProcessor */
            $config = $processor->getConfig();

            if (preg_match($config['file'], $path)) {
                return $processor;
            }
        }
    }
}