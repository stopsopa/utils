<?php


namespace Stopsopa\UtilsBundle\Lib\FileProcessors\Tools;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Stopsopa\UtilsBundle\Lib\FileProcessors\Tools\AbstractFileProcessor;
use Stopsopa\UtilsBundle\Lib\UtilFormAccessor;

class UploadResult {
    /**
     * @var Form
     */
    protected $errors;
    /**
     * @var BaseRequest
     */
    protected $return;

    protected $field;
    protected $id;
    protected $path;
    protected $request;
    public function __construct($field, $id, BaseRequest $request) {
        $this->field        = $field;
        $this->id           = $id;
        $this->request      = $request;
        $this->errors       = array();
    }

    public function addError($message) {
        $this->errors[] = $message;
    }
    public function setResponse($return) {
        $this->return = $return;
    }
    public function getErrors() {
        return $this->errors;
    }
    public function countErrors() {
        return count($this->errors);
    }
    function getPath() {
        return $this->path;
    }
    function setPath($path) {
        nieginie($path);
        niechginie($this->request->request->all());
        $this->path = $path;

//        niechginie($this->form->getData());

//        nieginie($this->field);
//        nieginie($path);
//        niechginie($this->form->getData());
//        UtilFormAccessor::setValue($this->form, $this->field, $path);
//        niechginie(UtilFormAccessor::getValue($this->form, $this->field));
        return $this;
    }
}