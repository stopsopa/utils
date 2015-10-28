<?php

namespace Stopsopa\UtilsBundle\Lib\Paginator;

/**
 * Cms\BaseBundle\Lib\Paginator\ButtonsList
 */
class ButtonsList {
    protected $buttons;
    protected $current;
    /**
     * Ile stron w bazie
     * @var integer
     */
    protected $pages;


    /**
     * @param array $list
     */
    public function __construct($list,$pages,$current) {
        $this->pages       = $pages;
        foreach ($list as $b) {
            /* @var $b Button */
            $b->getNum() == $current and $b->setCurrent(true);
        }
        $this->current = $current;
        $this->buttons = $list;
    }

    /**
     * Zwraca ile jest buttonów w podstawowym pasku - nei wliczając ostatniego
     * @return int
     */
    public function count() {
        return count($this->buttons);
    }
    /**
     * @return Button[]
     */
    public function getList() {
        return $this->buttons;
    }
    public function getPages () {
        return $this->pages;
    }
    public function getCurrent() {
        return $this->current;
    }
}
