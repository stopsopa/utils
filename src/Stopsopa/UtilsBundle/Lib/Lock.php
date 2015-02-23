<?php

namespace Stopsopa\UtilsBundle\Lib;

use Exception;

/**
 * Stopsopa\UtilsBundle\Lib\Lock.
 */
class Lock
{
    protected $file;
    protected $ch;
    public function __construct($file)
    {
        $this->file = $file;

        if (!file_exists($this->file)) {
            throw new Exception("File '".$this->file."' doesn't exist");
        }

        $class = $this;

        register_shutdown_function(function () use ($class) {
            $class->unlock();
        });
    }
    /**
     * http://www.tuxradar.com/practicalphp/8/11/0.
     *
     * Zwraca true jeśli
     *
     * @param int $seconds - jeśli ustawimy ten parametr
     *
     * @return boolean|Lock - zależy od tego czy podamy parametr seconds
     *
     * @throws Exception
     */
    public function lock($seconds = null, $throw = true)
    {
        $this->ch = fopen($this->file, 'r');

        if ($seconds = abs((int) $seconds)) {
            $time = time();
            while ((time() - $time) < $seconds) {
                if (flock($this->ch, LOCK_EX | LOCK_NB)) {
                    return true;
                }

                sleep(1);
            }

            if ($throw) {
                throw new Exception("Can't lock critical section on file '".$this->file."'");
            }

            return false;
        }

        flock($this->ch, LOCK_EX);

        return $this;
    }
    public function unlock()
    {
        $this->ch or ($this->ch = fopen($this->file, 'r'));

        flock($this->ch, LOCK_UN);
        fclose($this->ch);
        $this->ch = null;

        return $this;
    }
}
