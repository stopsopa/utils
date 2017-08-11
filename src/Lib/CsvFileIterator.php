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
    protected $return;

    /**
     * CsvFileIterator constructor.
     * @param $file
     * @param array $options
     *
     *    WARNING: use only if you have no newlines in data
     * @param callable|null $transform - optional function to process row before splitting it by "str_getcsv"
     *
     *    WARNING: use only if you have no newlines in data
     * @param callable|null $return - function that you can change structure of current iterated element
     *      default: function ($svg_row_as_array, $i, $raw_row_from_svg, $in_file_offset) {
     *          // $i - zero indexed number of row
     *          return $svg_row_as_array;
     *      }
     */
    public function __construct($file, $options = array(), Callable $transform = null, Callable $return = null)
    {
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

        $this->transform    = $transform;
        $this->return       = $return;

        if ($return && ! $this->transform) {

            $this->transform = function ($row) {

                return $row;
            };
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

            $offset = ftell($this->file);

            $row = call_user_func(
                $this->transform,
                $this->current
            );

            $this->current = str_getcsv(
                $row,
                $this->options['delimiter'],
                $this->options['enclosure'],
                $this->options['escape']
            );

            if ($this->return) {

                $this->current = call_user_func($this->return, $this->current, $this->key, $row, $offset);
            }
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
