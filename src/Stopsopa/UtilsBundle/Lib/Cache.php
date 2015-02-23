<?php

namespace Stopsopa\UtilsBundle\Lib;

use App;
use Exception;

class Cache
{
    protected static $param;
    protected static $config;
    protected static $root;
    public static function getRoot()
    {
        if (!self::$root) {
            self::$root = dirname(dirname(dirname(__FILE__)));
        }

        return self::$root;
    }
    public static function getCacheDir()
    {
        return self::getRoot().'/app/cache';
    }
    public static function getParamJson()
    {
        return static::getCacheDir().'/param.json';
    }
    public static function getParamYml()
    {
        return static::getRoot()."/app/hosts/".getHost().".yml";
    }
    public static function getConfigJson()
    {
        return static::getCacheDir().'/config.json';
    }
    public static function getConfigYml()
    {
        return static::getRoot()."/app/config.yml";
    }

    public static function clear()
    {
        $dir = self::getCacheDir();

        UtilFilesystem::checkDir($dir, true);

        App::system("rm -rf $dir/*");
    }
    public static function getParam($key = null)
    {

        // specjalne parametry
        if ($key === 'root') {
            return App::getRoot();
        }

        if (!static::$param) {
            $file = static::getParamJson();

            if (!file_exists($file)) {
                file_put_contents($file, json_encode(Yaml::parse(static::getParamYml())));
            }

            static::$param = json_decode(file_get_contents($file), true);
        }

        if (!$key) {
            return static::$param;
        }

        if (array_key_exists($key, static::$param)) {
            return static::$param[$key];
        }

        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key, 2);
            $data = static::getParam($parts[0]);

            return UtilArray::cascadeGet($data, $parts[1]);
        }

        throw new Exception("Param '$key' not found");
    }
    public static function getConfig($key = null)
    {
        if (!static::$config) {
            $file = static::getConfigJson();

            if (!file_exists($file)) {
                file_put_contents($file, json_encode(Yaml::parse(static::getConfigYml())));
            }

            static::$config = json_decode(file_get_contents($file), true);
            static::_bindConfig(self::$config);
            static::_bindConfig(self::$config);
        }

        if (!$key) {
            return static::$config;
        }

        if (array_key_exists($key, static::$config)) {
            return static::$config[$key];
        }

        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key, 2);
            $data = static::getConfig($parts[0]);
            $data = UtilArray::cascadeGet($data, $parts[1]);
            if ($data) {
                return $data;
            }
        }

        throw new Exception("Config '$key' not found");
    }
    protected static function _bindConfig(&$config = null)
    {
        if (is_string($config)) {
            preg_match_all('#((?<!\\\\)%.*?(?<!\\\\)%)#', $config, $matches);
            if (count($matches[0])) {
                foreach ($matches[0] as $key) {
                    try {
                        $data = static::getParam(trim(str_replace('\\%', '%', $key), '%'));
                        if (is_string($data)) {
                            $config = str_replace($key, $data, $config);
                        } else {
                            ($config === $key) and ($config = $data);
                        }
                    } catch (Exception $ex) {
                    }
                }
            }

            return;
        }

        if (is_array($config)) {
            foreach ($config as $key => &$val) {
                static::_bindConfig($val);
            }
        }
    }
}
