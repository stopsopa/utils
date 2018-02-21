<?php

namespace Stopsopa\UtilsBundle\Lib\Standalone;

use Exception;

class UtilFilesystem
{
    /**
     * See also: http://stackoverflow.com/a/3352564/5560682
     */
    public static function deleteEntireDir($directory, $empty = false)
    {
        // [http://lixlpixel.org/recursive_function/php/recursive_directory_delete/] g(delete directory recursively php)
        if (substr($directory, -1) == '/') {
            $directory = substr($directory, 0, -1);
        }
        if (!file_exists($directory) || !is_dir($directory)) {
            return false;
        } elseif (!is_readable($directory)) {
            return false;
        } else {
            $handle = opendir($directory);
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    $path = $directory.'/'.$item;
                    if (is_dir($path)) {
                        self::deleteEntireDir($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            closedir($handle);
            if ($empty == false) {
                if (!rmdir($directory)) {
                    return false;
                }
            }

            return true;
        }
    }
    public static function checkIfFileExistOrICanCreate($file, $createIfNotExist = false)
    {
        if (file_exists($file)) {
            return static::checkFile($file, true);
        } else {
            $dir = dirname($file);
            try {
                $result = static::checkDir($dir, true);
                if ($result) {
                    $createIfNotExist and touch($file);
                }
                return $result;
            } catch (Exception $ex) {
                throw new Exception("Cant create file: '$file' - reason: ".$ex->getMessage());
            }
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
    public static function removeDirIfEmpty($dir)
    {
        if (static::isEmptyDir($dir)) {
            rmdir($dir);

            return true;
        }

        return false;
    }

    public static function isEmptyDir($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        if (!is_readable($dir)) {
            return;
        }

        return (count(scandir($dir)) == 2);
    }
    /**
     * Remove empty directory up to path
     *
     UtilFilesystem::removeEmptyDirsToPath(
     pathinfo($file, PATHINFO_DIRNAME),
     $this->getUploadRootDir()
     );
     *
     * from state :
     .
     `-- user
     |-- d3
     |   `-- 5d
     `-- e8
     `-- d9
     `-- Clipboard02-kopiajfdksla-fds-afjdksla-fdsa-f-d-safdsa-f-d-sa-fd-s-af-d-sa-f-ds-a-fdusa-f-dsa-f-ds-a-fd-sa.bmpddd

     * removeEmptyDirsToPath('/var/docker/www/main/web/media/uploads/d3/5d', '/var/docker/www/main/web/media/uploads')
     *
     * to state
     .
     `-- user
     `-- e8
     `-- d9
     `-- Clipboard02-kopiajfdksla-fds-afjdksla-fdsa-f-d-safdsa-f-d-sa-fd-s-af-d-sa-f-ds-a-fdusa-f-dsa-f-ds-a-fd-sa.bmpddd
     *
     *
     *
     * @param type $dir
     * @param type $path
     */
    public static function removeEmptyDirsToPath($dir, $path)
    {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        while ($dir != $path) {
            if (!static::removeDirIfEmpty($dir)) {
                break;
            }

            $dir = pathinfo($dir, PATHINFO_DIRNAME);
        }
    }
    /**
     * Move method but it will create directory if doesn't eist
     *
     * @param type $source
     * @param type $target
     */
    public static function rename($source, $target, $mode = 0770)
    {
        $dir = dirname($target);

        if (!file_exists($dir)) {
            static::mkDir($dir, true, $mode);
        }

        return rename($source, $target);
    }

    /**
     * Flag switcher from /*id* /!!1 to /*id* /!!0 and back
     * @param string $file
     * @param string $id
     */
    public static function toggleFlag($file, $id, $forceState = null) {

        static::checkFile($file, $toWrite = true);

        $state = static::toggleFlagGetState($file, $id);

        if ($state === null) {
            throw new Exception("Flag '$id' not exist in file '$file'");
        }

        $t      = file_get_contents($file);

        $id     = preg_quote($id, '#');

        $s = $state;

        if (is_bool($forceState)) {
            if ($forceState === !$state) { // ustawiam stan na podany w force
                $t = preg_replace("#(?<=/\*$id\*/!!)".intval($state)."#", ''.intval(!$state), $t);

                $s = !intval($state);
            }
        }
        else { // toggle state
            if ($state) {
                $t = preg_replace("#(?<=/\*$id\*/!!)1#", '0', $t);
                $s = false;
            }
            else {
                $t = preg_replace("#(?<=/\*$id\*/!!)0#", '1', $t);
                $s = true;
            }
        }

        if ($state !== $s) {
            file_put_contents($file, $t);
        }
    }
    /**
     * Return state of flag
     * @param string $file
     * @param string $id
     * @return bool|null - null: no flag, true: flag is on, false: flag is off
     */
    public static function toggleFlagGetState($file, $id) {

        static::checkFile($file, $toWrite = true);

        $content = file_get_contents($file);

        $id = preg_quote($id, '#');

        preg_match("#/\*$id\*/!!([0|1])#", $content, $match);

        if (isset($match['1'])) {
            return !!intval($match['1']); // return state of flag
        }

        return null; // no flag in file
    }
}
