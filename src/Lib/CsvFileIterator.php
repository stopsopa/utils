<?php

namespace Stopsopa\UtilsBundle\Lib;

use Stopsopa\UtilsBundle\Lib\Standalone\UtilFilesystem;
use Iterator;

/*

AbstractException::setErrorHandler();

$file = '2017-08-10-dump.txt';

$csv = new CsvFileIterator($file, array(
    'delimiter' => "\t",
    'escape' => '\\',
), function ($row) {
    echo "test\n";
    print_r($row);
    return $row;
});

$row = 0;
$headers = array();

foreach ($csv as $item) {

    if ($row === 0) {
        $headers = $item;
    }
    else {
        try {
            $item = array_combine($headers, $item);
        }
        catch (Exception $e) {
            if ( strpos($e->getMessage(), 'Both parameters should have an equal number of elements') !== false ) {
                var_dump(array(
                    $headers,
                    $item
                ));
                die('end');
            }
            else {
                throw $e;
            }
        }
        print_r($item);
    }

    if ( ($row % 100000) === 0 ) {
        print_r($item);
    }
    if ( ($row % 1000) === 0 ) {
        echo "$row ";
    }

    if ($row > 5) {

        break;
    }

    $row += 1;
}
*/
class CsvFileIterator implements Iterator
{
    protected $file;
    protected $key = 0;
    protected $current;
    protected $valid;
    protected $options;
    protected $transform;

    /**
     * CsvFileIterator constructor.
     * @param $file
     * @param array $options
     * @param callable|null $transform - optional function to process row before splitting it by "str_getcsv"
     */
    public function __construct($file, $options = array(), Callable $transform = null)
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

        $this->transform = $transform;
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

        if ($this->transform) {

            if ($this->options['length']) {
                $this->current = fgets(
                    $this->file,
                    $this->options['length']
                );
            }
            else {
                $this->current = fgets($this->file);
            }

            $this->current = str_getcsv(
                call_user_func(
                    $this->transform,
                    $this->current
                ),
                $this->options['delimiter'],
                $this->options['enclosure'],
                $this->options['escape']
            );
        }
        else {
            $this->current = fgetcsv(
                $this->file,
                $this->options['length'],
                $this->options['delimiter'],
                $this->options['enclosure'],
                $this->options['escape']
            );
        }
        $this->key += 1;
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
