<?php

namespace Stopsopa\UtilsBundle\Composer;

use Stopsopa\UtilsBundle\Lib\AbstractApp;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class UtilHelper
{
    /**
     * @param type   $pattern
     * @param string $namespacepart - można podać kawałek namespace np: /Console/
     *
     * @return type
     */
    public static function findClasses($fileregexp, $classregexp, $notclassregexp = null)
    {
        $path = realpath(AbstractApp::getRootDir());

        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($objects as $file => $object) {
            /* @var $object SplFileInfo */
            if (!$object->isLink() && $object->isFile() && preg_match($fileregexp, $file)) {
                //                echo "$file - file\n";
                require_once $file;
            }
        }

        $list = array();
        foreach (get_declared_classes() as $namespace) {
            if (preg_match($classregexp, $namespace)) {
                if ($notclassregexp && preg_match($notclassregexp, $namespace)) {
                    continue;
                }

//                echo "$namespace\n";             
                $list[] = $namespace;
            }
        }

        return $list;
    }
}
