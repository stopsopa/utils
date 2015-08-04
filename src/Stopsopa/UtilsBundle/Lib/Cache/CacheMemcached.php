<?php

namespace Stopsopa\UtilsBundle\Lib\Cache;

class CacheMemcached extends AbstractCache
{
    protected $hash;
    public function __construct($hash)
    {
        $this->hash = $hash;
    }
    public function __destruct()
    {
        if ($this->save) {
            //            niechginie('tst');
            $this->save = false;
            MemcacheService::getMemcache()->set($this->hash, $this);
        }
    }
}
