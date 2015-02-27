<?php

namespace Stopsopa\UtilsBundle\Lib;

use Exception;
use Stopsopa\UtilsBundle\Exception\NoFrameworkException;
use ReflectionClass;

// klasy do przerzucenia bo wymuszają zależności
use Symfony\Component\HttpKernel\Kernel;
use AppCache;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Stopsopa\UtilsBundle\Lib\AbstractApp
 */
class AbstractApp
{
    /**
     * Lista domyślnych serwisów symfonowych, najczęściej używanych
     */
    const SERVICE_CONTAINER      = 'service_container'; // ContainerInterface
    const SERVICE_SECURITY       = 'security.context';
    const SERVICE_SESSION        = 'session';
    const SERVICE_EM             = 'doctrine.orm.default_entity_manager';
    const SERVICE_DBAL           = 'doctrine.dbal.default_connection';
    const SERVICE_TRANSLATOR     = 'translator';
    const SERVICE_ROUTER         = 'router';
    const SERVICE_REQUEST        = 'request';
    CONST SERVICE_ENGINE         = 'site.engine';
    CONST SERVICE_TEMPLATING     = 'templating';
    CONST SERVICE_VERSIONED      = 'service.cmsbase.versioned.service';
    CONST SERVICE_DBALLIGHT      = 'cmsbase.dballight.service';    
    
    protected static $_kernel;
    /**
     * @return Kernel
     */
    public static function getKernel()
    {
        if (!static::$_kernel) {
            global $kernel;
            
            if (!isset($kernel)) 
                throw new NoFrameworkException("UtilsBundle nie uzyskał dostępu do komponentów symfony");
            
            static::$_kernel = $kernel;
            if ($kernel instanceof AppCache) {
                static::$_kernel = $kernel->getKernel();
            }
        }

        return static::$_kernel;
    }
    /**
     * @return ContainerInterface
     */
    public static function getCont()
    {
        return static::getKernel()->getContainer();
    }

    public static function isDev()
    {
        return static::getCont()->getParameter('kernel.environment') == 'dev';
    }

    public static function isProd()
    {
        return static::getCont()->getParameter('kernel.environment') == 'prod';
    }
    public static function get($service)
    {
        return static::getCont()->get($service);
    }

    /**
     * Pobieranie parametru
     *
     * @param string $name nazwa parametru
     * @return mixed
     */
    public static function getParam($name) {
        
        if ($param = static::getCont()->hasParameter($name)) 
            return static::getCont()->getParameter($name);      

        if (strpos($name, '.') !== false) {
            $parts = explode('.', $name, 2);
            $data = static::getCont()->getParameter($parts[0]);
            $data = UtilArray::cascadeGet($data, $parts[1]);
            if ($data) 
                return $data;          
        }
        return static::getCont()->getParameter($name);
    }
    /**
     * Zwraca ścieżkę absolutną do templatki twig wskazanej notacją symfonową
     * PtCommonBundle:Default:article.html.twig -> /home/www/test/runtime/src/Pt/CommonBundle/Resources/views/Default/article.html.twig
     * @param type $sfname
     * @return string
     * @throws Exception
     */
    public static function getTwigPathnameBySymfonyPath($sfname) {
        $parts = explode(':', $sfname);

        if (count($parts) != 3)
            throw new Exception("Twig symfony path '$sfname' is invalid.");

        $path = static::getKernel()->getBundle($parts[0])->getPath();

        $path .= '/Resources/views';
        $path .= '/'.$parts[1];
        $path .= '/'.$parts[2];

        return $path;
    }
    protected static $root;

    /**
     * Zwraca ścieżkę do katalogu głównego projektu
     * @param bool $bundlepath - def: false, true - absolute path to current bundle
     * @return string
     */
    public static function getRootDir($bundlepath = false) {
        
        // przyspieszenie
        if (static::$root) 
            return static::$root;
        
        try {
            $dir = dirname(static::getCont()->getParameter('kernel.root_dir'));

            if ($bundlepath) {
                return static::getKernel()->getBundle($bundlepath)->getPath();
    //            $n = get_called_class();
    //            $n = substr($n, 0, -strlen(strrchr($n, '\\')));
    //            $n = substr($n, 0, -strlen(strrchr($n, '\\')));
    //            $n = str_replace('\\', '/', $n);
    //            $dir .= "/src/$n";
            }

            return $dir;            
        } catch (NoFrameworkException $ex) {
            
            // nie wiem czy to najlepsze ale najwyżej później to wymienie
            if (!static::$root) {
                $reflection = new ReflectionClass('Composer\Autoload\ClassLoader');
                $file = $reflection->getFileName();
                static::$root = dirname(dirname(dirname($path)));
            }            
            
            return static::$root;
        }
    }
    /**
     * ---------- wyleci do AppGenerated.php
     * tam gdzie będzie AbstractEntity
     * 
     * 
     * 
     * Wywoływać 
     * $this->getClassMetadata($this); -- jeśli z wywoływane z wewnątrz AbstractManager
     * lub 
     * App::getClassMetadata('string'|object);
     * @param type $class
     * @return type
     * @throws Exception
     */
    public static function getClassMetadata($class = null, $em = 'default') {
        if (is_object($class)) {
            throw new Exception("Aby podać obiekt jako pierwszy argument do metody getClassMetadata() trzeba nadpisać metodę w AppGenerated.php");
//            if ($class instanceof AbstractManager) 
//                /* @var $class AbstractManager */
//                $class = $class->getClass();          
//            else           
//                $class = AbstractEntity::getClassNamespace($class);          
        }

        if (!$class) 
            throw new Exception("Parameter class is not string, is: ".  gettype($class));

        return static::getEntityManager($em)->getClassMetadata($class);
    }
    /**
     * Wywoływać 
     * $this->getTableNameByClass($this); -- jeśli z wywoływane z wewnątrz AbstractManager
     * lub 
     * App::getTableNameByClass('string'|object);
     * 
     * Całkiem możliwe że trzeba będzie to wywalić i po prostu po ludzku zdefiniować nazwy tabel na sztywno w encjach
     * bo całe zamieszanie powstaje przy generowaniu nazw tabel na podstawie encji
     *
     * Metoda do pobierania nazwy tabeli zależnie od systemu operacyjnego dla zapytań natywnych sql
     * Niestety windows niezależnie od encji tworzy nazwy tabel lowercase
     * bo nazwa tabeli jest zależna od systemu plików
     * ...linux rozróżnia wielkość liter w nazwach plików a windows nie
     * @return type
     */
    public static function getTableNameByClass($class = null) {      
        $tableName = static::getClassMetadata($class)->getTableName();

        if (preg_match('/win/', strtolower(PHP_OS)))
            return strtolower($tableName);

        return $tableName;
    }
    

    /**
     * @param string $type default or forum
     * @return EntityManager
     */
    public static function getEntityManager($type = 'default') {
        return static::get("doctrine.orm.{$type}_entity_manager");
    }
    /**
     * @param string $type
     * @return Connection
     */
    public static function getDbal($type = 'default') {
        return static::getEntityManager($type)->getConnection();
    }
    
    
    
    
    /**
     * @return Router
     */
//    public static function getRouter() {
//        return static::get('router');
//    }
//
//    /**
//     * @return SecurityContext
//     */
//    public static function getSecurity() {
//      return static::get(AbstractService::SERVICE_SECURITY);
//    }
    /**
     * @return User
     */
//    public static function getUserFromContext() {
//        if (!static::getSecurity())
//            return null;
//
//        $token = static::getSecurity()->getToken(); /* @var $token TokenInterface */
//        if ($token && is_object($token->getUser()))
//            return $token->getUser();
//
//        return null;
//    }
    /**
     * @return Request
     */
//    public static function getRequest() {
//      return static::get(static::SERVICE_REQUEST);
//    }
//    public static function isGoogleBoot($request = null) {
//      /* @var $request Request */
//      if (!$request) 
//        $request = static::getRequest();
//      
//      return ( preg_match('/Googlebot/', $request->server->get('HTTP_USER_AGENT', '')) );
//    }
    /**
     * @return Session
     */
//    public static function getSession() {
//      return static::get(self::SERVICE_SESSION);
//    }
//    /**
//     * @return FlashBag
//     */
//    public static function getFlashBag() {
//      return static::getSession()->getFlashBag();
//    }
//    /**
//     * @return User
//     */
//    public static function getUser() {
//      $token = static::getSecurity()->getToken();
//      if ($token) {
//        if (is_object($token->getUser())) {
//          return $token->getUser();
//        }
//      }
//      return false;
//    }
//    public static function trans($id, $parameters = null, $domain = null, $locale = null) {	
//	if (is_string($parameters)) {
//	    $domain = $parameters;
//	    $parameters = array();
//	}
//        return static::getServiceTranslator()->trans($id, $parameters, $domain, $locale);
//    }
//    public static function getTwigDir($sfname) {
//        $parts = explode(':', $sfname);
//        
//        if (count($parts) != 3)
//            throw new Exception("Twig symfony path '$sfname' is not valid");
//        
//        if ($parts[0]) {
//            $dir = self::getRootDir($parts[0])."/Resources/views";
//        }
//        else {
//            $dir = self::getRootDir()."/app/Resources/views";
//        }
//        
//        if ($parts[1]) 
//            return $dir."/".$parts[1];
//        
//        return $dir;
//    }

//    public static function getTemplateBody($sfname) {
//        $dir = self::getTwigDir($sfname);
//        $parts = explode(':', $sfname);
//        $file = "$dir/$parts[2]";
//        
//        if (file_exists($file)) 
//            return file_get_contents($file);
//        
//        throw new Exception("Template '$sfname' not found");
//    }
    /**
     * Sprawdza czy podany tekst jest prawidłową ścieżką do twig
     * np: 'CmsBundle:Admin:index.html.twig'
     */
//    public static function isTemplateValidSfPath($source) {
//        
//        if (strpos($source, "\n") !== false) 
//            return false;
//        
//        return (bool)preg_match('#^([a-z_\-]*)?\:([a-z_\-]*)?\:[a-z_\-\\\\.]+$#i', $source);
//    }
    /**
     * Tłumaczenia w portalu - pliki yml, xml
     * @return Translator
     */
//    public static function getServiceTranslator() {
//      return static::get(static::SERVICE_TRANSLATOR);      
//    }
//    /**
//     * Tłumaczenia encji systemu cms
//     * @return VersionedService
//     */
//    public static function getServiceVersioned() {
//      return static::get(static::SERVICE_VERSIONED);      
//    }
//    /**     
//     * @return TwigEngine
//     */
//    public static function getServiceTemplating() {
//      return static::get(static::SERVICE_TEMPLATING);
//    }
//    /**
//     * @return DumperService
//     */
//    public static function getServiceDumper(){
//      return static::get(DumperService::SERVICE);
//    }
//    /**
//     * @return SiteEngine
//     */
//    public static function getServiceEngine() {
//        return static::get(static::SERVICE_ENGINE);
//    }  
//    
//    public static function getRoutingParams($name) {
//        $service = self::getRouter();
//        /* @var $data Route */
//        $data = $service->getRouteCollection()->get($name);
//        if ($data) {
//            preg_match_all('#\{([^}]+)\}#i', $data->getPath(), $matches);
//            if (@is_array($matches[1])) 
//                return $matches[1];
//            
//            return array();
//        }
//        
//        throw new Exception("Not found Route by name: '$name'");
//    }

//    public static function isRoutingGeneratableWithoutArguments($name) {
//        $service = self::getRouter();
//        /* @var $data Route */
//        $data = $service->getRouteCollection()->get($name);
//        if ($data) {
//            foreach (self::getRoutingParams($name) as $param) {
//                if ($data->getDefault($param) === null) 
//                    return false;
//            }
//            return true;
//        }
//        
//        
//        throw new Exception("Not found Route by name: '$name'");
//    }
//    /**
//     * @return DbalLightService
//     */
//    public static function getServiceDbalLight() {
//        return self::get(self::SERVICE_DBALLIGHT);
//    }
}
