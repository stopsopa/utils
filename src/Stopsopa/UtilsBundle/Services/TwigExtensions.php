<?php

namespace Stopsopa\UtilsBundle\Services;

use AppBundle\Entity\AdManager;
use Symfony\Component\DependencyInjection\Container;
use Twig_Extension;
use Twig_SimpleFunction;
use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilFilesystem;
use DateTime;
use Exception;

/**
 * Stopsopa\UtilsBundle\Services\TwigExtensions
 */
class TwigExtensions extends Twig_Extension
{
    /**
     * @var Container
     */
    protected $container;

    public function getName() {
        return 'stopsopa_twig_extensions';
    }
    public function __construct(Container $container) {
        $this->container = $container;
    }
//    public function getGlobals() {
//      // http://symfony.com/doc/current/cookbook/templating/global_variables.html
//  //      echo '<pre>';
//  //      die(print_r(parent::getGlobals()));
//      return array_merge(parent::getGlobals(),array(
//          'core' => new Core($this->container)
//      ));
//      return ;
//    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
      return array(
//            new Twig_SimpleFunction('ad', array($this, 'getAd'), array('is_safe' => array('html'))),
            new Twig_SimpleFunction('param', array($this, 'param'), array('is_safe' => array('html'))),
            new Twig_SimpleFunction('ver', array($this, 'ver'), array('is_safe' => array('html'))),
  //        '__formparams' => new Twig_Function_Method($this, '__formparams'),
  //        'getPath'           => new Twig_Function_Method($this, 'getPath',        array('is_safe' => array('html'))),
  //        'getUrl'            => new Twig_Function_Method($this, 'getUrl' ,        array('is_safe' => array('html'))),
      );
    }
    protected $ver;
    public function ver() {
        if (!$this->ver) {
            $file = AbstractApp::getRootDir().'/app/cache/prod/appProdUrlMatcher.php';

            if (!file_exists($file)) {
                die("File '$file'' not exists, please run project in production mode once to create it");
            }

            UtilFilesystem::checkFile($file);

            $d = new DateTime(date("c", filemtime($file)));

            $this->ver = $d->format('Y-m-d-H-i-s');
        }
        return $this->ver;
    }
//    public function getAd($key) {
//        return $this->container->get(AdManager::SERVICE)->getAd($key);
//    }
    public function param($key) {
        if ($this->container->hasParameter($key)) {
            return $this->container->getParameter($key);
        }
        throw new Exception("Brak parametru w parameters.yml o nazwie '".$key."'");
    }
    /**
     * simon
     * @return type
     */
//    public function getFilters() {
//        return array(
////          'hlight'      => new Twig_Filter_Method($this, 'hlight', array('is_safe' => array('html'))),
////          'niechginie'  => new Twig_Filter_Function('niechginie'),
////          'cut'         => new Twig_Filter_Method($this, 'cut', array('is_safe' => array('html'))),
////          'fixlasttags' => new Twig_Filter_Method($this, 'fixlasttags', array('is_safe' => array('html')))
//        );
//    }
}