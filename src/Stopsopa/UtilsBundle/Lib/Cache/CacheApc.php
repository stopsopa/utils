<?php

namespace Stopsopa\UtilsBundle\Lib\Cache;

class CacheApc extends AbstractCache {
    protected $hash;
    public function __construct($hash) {
        $this->hash = $hash;
    }
    public function __destruct() {
        if ($this->save) {
            $this->save = false;
            apc_store($this->hash, $this);         
        }
    }
}