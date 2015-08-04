<?php

namespace Stopsopa\UtilsBundle\Services;

use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

/**
 * Stopsopa\UtilsBundle\Services\TwigFilesystemLoader.
 */
class TwigFilesystemLoader extends FilesystemLoader
{
    public function __construct(FileLocatorInterface $locator, TemplateNameParserInterface $parser)
    {
        $this->dev = AbstractApp::isDev();
        $this->loader = AbstractApp::getStpaConfig('yui.symlinkloader', '');
        $this->method = AbstractApp::getStpaConfig('yui.transformmethod', false);
        parent::__construct($locator, $parser);
    }
    public function getSource($name)
    {
        if ($this->dev) {
            return $this->{$this->method}(parent::getSource($name));
        }

        return parent::getSource($name);
    }

    protected function _replaceAssetsBundles($data)
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
                        //[m1]        [m3]<link rel="stylesheet" href="[l]/bundles/stopsopautils/asset.php/[o]asset/sp.css=[e]bundles/app/theme/theme/bootstrap/dist/css/bootstrap.css[m4]" type="text/css"/>
//[m1]        [m3]<link rel="stylesheet" href="[l]/bundles/stopsopautils/asset.php/[o]asset/sp.css=[e]bundles/app/theme/theme/jquery.gritter/css/jquery.gritter.css[m4]" type="text/css"/>
//                        $l[$k] = '[m1]'.$match[1].'[m3]'.$match[3].'[l]'.$loader.'[o]'.$output."=".'[e]'.$e.'[m4]'.$match[4];


                        $l[$k] = $match[1].$match[3].'/'.$e.$match[4];
                    }

                    return implode("\n", $l)."\n";
                }

                return "\n";
            }, $data);
        }

        return $data;
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
                        //[m1]        [m3]<link rel="stylesheet" href="[l]/bundles/stopsopautils/asset.php?build=[o]asset/sp.css=[e]bundles/app/theme/theme/bootstrap/dist/css/bootstrap.css[m4]" type="text/css"/>
//[m1]        [m3]<link rel="stylesheet" href="[l]/bundles/stopsopautils/asset.php/[o]asset/sp.css=[e]bundles/app/theme/theme/jquery.gritter/css/jquery.gritter.css[m4]" type="text/css"/>
                        $l[$k] = $match[1].$match[3].$loader.$output.'&asset='.$e.$match[4];
                    }

                    return implode("\n", $l)."\n";
                }

                return "\n";
            }, $data);
        }

        return $data;
    }
}
