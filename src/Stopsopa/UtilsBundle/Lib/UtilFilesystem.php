<?php

namespace Stopsopa\UtilsBundle\Lib;

use Exception;

class UtilFilesystem
{
    public static function checkIfFileExistOrICanCreate($file, $createIfNotExist = false)
    {
        if (file_exists($file)) {
            static::checkFile($file, true);
        } else {
            $dir = dirname($file);
            try {
                return static::checkDir($dir, true);
            } catch (Exception $ex) {
                throw new Exception("Cant create file: '$file' - reason: ".$ex->getMessage());
            }
            $createIfNotExist and touch($file);
        }
    }
    public static function checkDir($dir, $toWrite = false)
    {
        if (!file_exists($dir)) {
            throw new Exception("Directory not exist: '$dir'", 1);
        }

        if (!is_dir($dir)) {
            throw new Exception("Path '$dir' is not directory", 1);
        }

        if ($toWrite) {
            if (!is_writable($dir)) {
                throw new Exception("Directory '$dir' is not writtable", 1);
            }
        } else {
            if (!is_readable($dir)) {
                throw new Exception("Directory '$dir' is not readable", 1);
            }
        }

        return true;
    }
    public static function checkFile($file, $toWrite = false, $isDeletable = false)
    {
        $dir = dirname($file);

        $isDeletable and static::checkDir($dir, true);

        if (!file_exists($file)) {
            throw new Exception("File not exist: '$file'", 1);
        }

        if (!is_file($file)) {
            throw new Exception("Path '$file' is not file", 1);
        }

        if ($toWrite) {
            if (!is_writable($file)) {
                throw new Exception("File '$file' is not writtable", 1);
            }
        } else {
            if (!is_readable($file)) {
                throw new Exception("File '$file' is not readable", 1);
            }
        }

        return true;
    }
    public static function mkDir($dir, $toWrite = false, $mode = 0770)
    {
        if (!file_exists($dir)) {
            mkdir($dir, $mode, true);
        }
        static::checkDir($dir, $toWrite);

        return true;
    }
    public static function removeFile($file)
    {
        if (file_exists($file)) {
            static::checkFile($file, true);
            unlink($file);
        }

        return true;
    }

    public static function removeFileIfEmpty($file)
    {
        if (file_exists($file)) {
            static::checkFile($file, true);

            if (!filesize($file)) {
                unlink($file);
            }
        }

        return true;
    }
}
