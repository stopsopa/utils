<?php

namespace Stopsopa\UtilsBundle\Composer;
use Stopsopa\UtilsBundle\Lib\AbstractApp;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ComposerHelper {
    public static function findClasses($pattern) {        
        $path = realpath(AbstractApp::getRootDir());
        
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        
        foreach ($objects as $file => $object) {            
            /* @var $object SplFileInfo */
            if (!$object->isLink() && $object->isFile() && strpos($file, "/$pattern.php") !== false) {
                require_once $file;                
            }
        }
        
        $list = array();
        foreach (get_declared_classes() as $namespace) {
            if (strpos($namespace, "\\".$pattern) !== false) {
                $list[] = $namespace;   
            }
        }      
        
        return $list;
    }
}