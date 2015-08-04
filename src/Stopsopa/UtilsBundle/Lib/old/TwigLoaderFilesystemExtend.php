<?php

namespace Stopsopa\UtilsBundle\Lib;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Error_Loader;
use Twig_Loader_Filesystem;

/**
 * Trochę burdelu się narobiło w tej klasie :/.
 *
 * Stopsopa\UtilsBundle\Lib\TwigLoaderFilesystemExtend
 */
class TwigLoaderFilesystemExtend extends Twig_Loader_Filesystem
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $symlinkloader;
    protected $loader;
    protected $dev;

//    protected function transform($name) {
//
//        if (strpos($name, '@') === 0) {
////            $name = '@WebProfiler/Profiler/jdjd/toolbar_js.html.twig';
//            preg_match('#^@([^/]+)/(.+)/([^/]+)$#', $name, $m);
//
//            $find = 'Resources/public/';
//            if (strpos($m[2], $find) === 0)
//                $m[2] = substr($m[2], strlen($find));
//
//            if (strpos($name, 'Bundle') === false) {
//                $m[1] .= 'Bundle';
//            }
//
//            $name = $m[1].':'.$m[2].':'.$m[3];
//
//            return $name;
//        }
//        else {
//            $k = explode(':', $name);
//            if (count($k) < 3) {
//                $name = '::'.$name;
//            }
//        }
//
//
//
//        return $name;
//    }

/**
 * {@inheritdoc}
 */
//    public function exists($name)
//    {
//        $p = explode(':', $this->transform($name));
//
//        if ($p[0]) {
//            $file = AbstractApp::getKernel()->getBundle($p[0])->getPath();
//        }
//        else {
//            $file = AbstractApp::getRootDir().'/app';
//        }
//
//        $file .= '/Resources/views/'.$p[1].'/'.$p[2];
//
//        return file_exists($file) ? $file : false;
//
////        $name = $this->normalizeName($name);
////
////        if (isset($this->cache[$name])) {
////            return true;
////        }
////
////        try {
////            $this->findTemplate($name);
////
////            return true;
////        } catch (Twig_Error_Loader $exception) {
////            return false;
////        }
//    }
    public function getCacheKey($name)
    {
        niechginie('jest');

        return parent::getCacheKey($name);

//        if ($this->isExc($name)) {
//            return parent::getCacheKey($name);
//        }
//
//        $name = $this->transform($name);
//
//        if (strpos($name, "\n") !== false) {
//            return $name;
//        }
//
//        try {
//            return parent::getCacheKey($name);
//    //      return parent::getCacheKey($name.$this->checkExt($name));
//        } catch (Twig_Error_Loader $e) {
//            //Cache Key is same of $name
//            return $name;
//        }
    }
    public function getSource($name)
    {
        niechginie('jest');
        if (AbstractApp::isDev()) {
            return $this->_replaceAssets(parent::getSource($name));
        }

        return parent::getSource($name);

//        if ($this->isExc($name)) {
//            return $this->_replaceAssets(parent::getSource($name));
//        }
//
//        $name = $this->transform($name);
//
//        if (strpos($name, "\n") !== false) {
//            return $this->_replaceAssets($name);
//        }
//
//        try {
//            return $this->_replaceAssets(parent::getSource($name));
//    //      return parent::getSource($name.$this->checkExt($name));
//        } catch (Twig_Error_Loader $e) {
//            //Always response is outdated
//            return $this->_replaceAssets($name);
//        }
    }
    public function isFresh($name, $time)
    {
        niechginie('jest');

        return parent::isFresh($name, $time);

//        if ($this->isExc($name)) {
//            return parent::isFresh($name, $time);
//        }
//
//        $name = $this->transform($name);
//
//        if (strpos($name, "\n") !== false) {
//            return $name;
//        }
//
//        try {
//            return parent::isFresh($name, $time);
//    //      return parent::isFresh($name.$this->checkExt($name),$time);
//        } catch (Twig_Error_Loader $e) {
//            //Always response is outdated
//            return false;
//        }
    }
    protected function _replaceAssets($data)
    {
        if ($this->dev && preg_match('#\{%\s+(?:stylesheets|javascripts)#is', $data)) {
            $loader = $this->loader;

            return preg_replace_callback('#([ ]*)\{%\s+(?:stylesheets|javascripts)((?:\s+[^\'"]*[\'"][^\'"]*[\'"])*?)\s+%\}([^\{]*?)\{\{\s*asset_url\s*\}\}([^\{]*?)\{%\s+end(?:stylesheets|javascripts)\s+%\}#is', function ($match) use ($loader) {
                if (substr_count($match[2], '"') > 1 || substr_count($match[2], "'") > 1) {
                    preg_match_all('#(\s+[^\'"]*[\'"][^\'"]*[\'"])*?#si', $match[2], $m);
                    $match[4] = rtrim($match[4]);
                    $l = array();
                    $output = '';
                    foreach ($m[0] as $d) {
                        $d = trim($d);
                        if ($d) {
                            if ($d[0] == '"' || $d[0] == "'") {
                                $l[] = trim($d, '\'"');
                            } elseif (substr($d, 0, 6) == 'output') {
                                $output = explode('=', $d);
                                $output = trim($output[1], '\'"');
                            }
                        }
                    }

//                    var_dump(array(
//                        $match[1],
//                        $match[3],
//                        $loader,
//                        $output
//                    ));

                    foreach ($l as $k => $e) {
                        $l[$k] = $match[1].$match[3].$loader.$output.'='.$e.$match[4];
                    }

                    return implode("\n", $l)."\n";
                }

                return "\n";
            }, $data);
        }

        return $data;
    }
//    protected function isExc($name) {
//
////        if (strpos($name, '@WebProfiler/Collector/') === 0) {
////            return true;
////        }
//
//        return preg_match('/Exception/i', $name);
//    }
}
