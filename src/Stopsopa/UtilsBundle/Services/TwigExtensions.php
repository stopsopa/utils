<?php

namespace Stopsopa\UtilsBundle\Services;

use AppBundle\Entity\AdManager;
use Symfony\Component\DependencyInjection\Container;
use Twig_Extension;
use Twig_SimpleFunction;

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
            new Twig_SimpleFunction('ad', array($this, 'getAd'), array('is_safe' => array('html'))),
            new Twig_SimpleFunction('param', array($this, 'param'), array('is_safe' => array('html'))),
  //        '__formparams' => new Twig_Function_Method($this, '__formparams'),
  //        'getPath'           => new Twig_Function_Method($this, 'getPath',        array('is_safe' => array('html'))),
  //        'getUrl'            => new Twig_Function_Method($this, 'getUrl' ,        array('is_safe' => array('html'))),
      );
    }
    public function getAd($key) {
        return $this->container->get(AdManager::SERVICE)->getAd($key);
    }
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
    public function getFilters() {
        return array(
//          'hlight'      => new Twig_Filter_Method($this, 'hlight', array('is_safe' => array('html'))),
//          'niechginie'  => new Twig_Filter_Function('niechginie'),
//          'cut'         => new Twig_Filter_Method($this, 'cut', array('is_safe' => array('html'))),
//          'fixlasttags' => new Twig_Filter_Method($this, 'fixlasttags', array('is_safe' => array('html')))
        );
    }
}