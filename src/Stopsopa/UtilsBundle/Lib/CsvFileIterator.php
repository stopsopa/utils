<?php

namespace Stopsopa\UtilsBundle\Lib;

use Stopsopa\UtilsBundle\Lib\Standalone\UtilFilesystem;
use Iterator;

/**
 }
 */
class CsvFileIterator implements Iterator
{
    protected $file;
    protected $key = 0;
    protected $current;
    protected $valid;
    protected $options;

    public function __construct($file, $options = array())
    {
        UtilFilesystem::checkFile($file);
        $this->file = fopen($file, 'r');
        if (is_array($options)) {
            $this->options = array_merge(array(
                'length' => 0,
                'delimiter' => ',',
                'enclosure' => '"',
                'escape' => '\\',
                'skip' => 0, // skip lines
            ), $options);
        }
    }

    public function __destruct()
    {
        fclose($this->file);
    }

    public function rewind()
    {
        rewind($this->file);
        $this->key = 0;

        if ($this->options['skip']) {
            for ($i = 0; $i < $this->options['skip']; ++$i) {
                $this->next();
            }
        }

        $this->next();
    }

    public function next()
    {
        $this->valid = !feof($this->file);
        $this->current = fgetcsv(
            $this->file,
            $this->options['length'],
            $this->options['delimiter'],
            $this->options['enclosure'],
            $this->options['escape']
        );
        ++$this->key;
    }

    public function valid()
    {
        return $this->valid;
    }

    public function key()
    {
        return $this->key;
    }

    public function current()
    {
        return $this->current;
    }
}
