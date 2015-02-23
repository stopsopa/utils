<?php

namespace Stopsopa\UtilsBundle\Lib;

class ApcCache
{
    protected $data;
    protected $hash;
    protected $save;
    public function __construct($hash)
    {
        $this->hash = $hash;
    }
    public function clear()
    {
        $this->save = true;
        $this->set(null, array());

        return $this;
    }
    public function set($key, $data)
    {
        $this->save = true;

        if (is_null($key)) {
            $this->data = $data;

            return $this;
        }

        if (!$this->data) {
            $this->data = array();
        }

        $this->data[$key] = $data;

        return $this;
    }
    public function get($key = null)
    {
        if (!$this->data) {
            $this->data = array();
        }

        if (is_null($key)) {
            return $this->data;
        }

        if (!array_key_exists($key, $this->data)) {
            throw new Exception("Key '$key' not found");
        }

        return $this->data[$key];
    }
    public function __destruct()
    {
        if ($this->save) {
            $this->save = false;
            apc_store($this->hash, $this);
        }
    }
}
