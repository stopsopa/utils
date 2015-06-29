<?php


namespace Stopsopa\UtilsBundle\Lib\FileProcessors\Tools;

use Stopsopa\UtilsBundle\Lib\Standalone\UtilArray;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as BaseRequest;

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
        $this->path = $path;

        $data = $this->request->request->all();

        UtilArray::cascadeSet($data, $this->field, $path);

        $this->request->request = new ParameterBag($data);

        return $this;
    }
}