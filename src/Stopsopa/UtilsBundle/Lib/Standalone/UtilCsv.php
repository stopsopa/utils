<?php

namespace Stopsopa\UtilsBundle\Lib\Standalone;

class UtilCsv {
    static $isbuildin;
    public static function putCsv($input, $delimiter = ',', $enclosure = '"') 
    {                
        if (static::$isbuildin === null) 
            static::$isbuildin = function_exists('str_putcsv');
          
        if (static::$isbuildin) 
            return str_putcsv($input, $delimiter, $enclosure);
        
        return static::_str_putcsv($input, $delimiter, $enclosure);        
    }
    protected static function _str_putcsv($input, $delimiter = ',', $enclosure = '"') {        
        // Open a memory "file" for read/write...
        $fp = fopen('php://temp', 'r+');
        // ... write the $input array to the "file" using fputcsv()...
        fputcsv($fp, $input, $delimiter, $enclosure);
        // ... rewind the "file" so we can read what we just wrote...
        rewind($fp);
        // ... read the entire line into a variable...
        $data = fread($fp, 1048576);
        // ... close the "file"...
        fclose($fp);
        // ... and return the $data to the caller, with the trailing newline from fgets() removed.
        return rtrim($data, "\n");
    }
}
