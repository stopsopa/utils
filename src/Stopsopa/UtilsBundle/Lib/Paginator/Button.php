<?php

namespace Stopsopa\UtilsBundle\Lib\Paginator;

/**
 * Cms\BaseBundle\Lib\Paginator\Button
 */
class Button {
    protected $label;
    protected $num;
    protected $isCurrent;


    /**
     * @param int $num
     * @param string|integer $label
     */
    public function __construct($num, $label) {
        $this->num = $num;
        $this->label   = $label;
        $this->isCurrent = false;
    }

    /**
     * @param string|int $label
     * @return Button
     */
    public function setLabel($label) {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }
    /**
     * @param int $num
     * @return Button
     */
    public function setNum($num) {
        $this->num = $num;
        return $this;
    }

    /**
     * @return int
     */
    public function getNum() {
        return $this->num;
    }

    /**
     * @param boolean $isCurrent
     * @return Button
     */
    public function setCurrent($isCurrent) {
        $this->isCurrent = $isCurrent;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCurrent() {
        return $this->isCurrent;
    }
}