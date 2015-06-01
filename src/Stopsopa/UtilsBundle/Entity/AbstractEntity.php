<?php

namespace Stopsopa\UtilsBundle\Entity;

use ArrayAccess;
use Cms\BaseBundle\BasicClass\ReflectionClass;
use Cms\BaseBundle\Lib\App;
use DateTime;
use DomainException;
use Exception;
use Symfony\Component\Security\Core\Util\ClassUtils;
//use FOS\UserBundle\Entity\Group;

/**
 * Cms\BaseBundle\Entity\AbstractEntity
 */
abstract class AbstractEntity {

    /**
     * Dla obsługi eventów doctrine2 livecycle events
     * @var boolean
     */
    public $_lcet;

    /**
     * Zwraca domyślny manager dla encji,
     * Można nadpisać tą metode już w konkretnej encji i zmodyfikować logikę
     * sięgania po manager
     * @param mixed $context
     * @return AbstractManager
     */
    public static function getEntityManager($context = null) {
      $class = static::cleanClassNamespace(get_called_class()).'Manager';
      $refl = new ReflectionClass($class);
      return App::get($refl->getConstant('SERVICE'));
    }

    /**
     * @return string Nazwa uprawnienia, które akceptuje daną encję
     */
//    public static function acceptingPermission(){
//        return Group::G_ADMIN;
//    }


    public function toArray() {
        return get_object_vars($this);
    }

    public function getProperties(array $fields) {
        $filtered = array();
        foreach ($fields as $field) {
            if ($field)
                if (!property_exists($this, $field) && !(method_exists($this, 'get' . ucfirst($field)))) {
                    throw new DomainException('Pole "' . $field . '" nie istnieje w klasie ' . get_class($this));
                }
            $method = 'get' . ucfirst($field);
            if (is_object($this->$method()) && $this->$method()) {
                if (method_exists($this->$method(), '__toString')) {
                    $filtered[$field] = $this->$method()->__toString();
                } else {
                    if ($this->$method() instanceof DateTime) {
                        $filtered[$field] = $this->$method()->format(self::DATE_TIME_FORMAT);
                    } else {
                        throw new DomainException('Klasa "' . get_class($this->$method()) . '" nie posiada metody __toString ');
                    }
                }
            } else {
                $filtered[$field] = $this->$method();
            }
        }
        return $filtered;
    }

    protected static $ENUMS = array();
    /**
     * Działa tylko na polach typu CONST nie działa na STATIC
     * Metoda helpera uzupełniająca funkcjonalność
     * Site\AbstractBundle\DBAL\EnumType
     * Nie chcesz nie używaj :P... (Kuba)
     * @param string $prefix - (ex: 'STAN')
     * @return array
     */
    public static function getEnumTypes($prefix) {
        if (!isset(static::$ENUMS[$prefix]) || !is_array(static::$ENUMS[$prefix]) || !static::$ENUMS[$prefix]) {
            static::$ENUMS[$prefix] = array();
            $name = static::getClassNamespace();
            $ref = new ReflectionClass($name);
            foreach ($ref->getConstants() as $k => $d){
                $matches = array();
                if (is_string($k) && preg_match('/^' . preg_quote($prefix) . '_(.*)/', $k, $matches))
                    static::$ENUMS[$prefix][$d] = $matches['1'];
            }
        }

        return static::$ENUMS[$prefix];
    }

    /**
     * Metoda helpera uzupełniająca funkcjonalność
     * Site\AbstractBundle\DBAL\EnumType
     * Nie chcesz nie używaj :P... (Kuba)
     * @param string $prefix - (ex: 'STAN')
     * @return array
     */
    public static function getStaticEnumTypes($prefix) {
        $s_prefix = 'static_'.$prefix;
        if (!isset(static::$ENUMS[$s_prefix]) || !is_array(static::$ENUMS[$s_prefix]) || !static::$ENUMS[$s_prefix]) {
            static::$ENUMS[$s_prefix] = array();
            $ref = new ReflectionClass(get_called_class());
            foreach ($ref->getStaticProperties() as $k => $d){
                $matches = array();
                if (is_string($k) && preg_match('/^' . preg_quote($prefix) . '_(.*)/', $k, $matches))
                    static::$ENUMS[$s_prefix][$matches['1']] = $d;
            }
        }

        return static::$ENUMS[$s_prefix];
    }

    /**
     * Niestety z powodu doctrine get_class() wykonane na encji potrafi zwrócić np:
     * Proxies\__CG__\Site\CMS\ArticleBundle\Entity\Article
     * ale można w łatwy sposób wyciągnąć z tego prawidłowy namespace do encji:
     * Site\CMS\ArticleBundle\Entity\Article
     * @param type $entity
     * @return type
     */
    public static function getClassNamespace($entity = null, $throwException = true) {

        if($entity === null) {
            $class = get_called_class();
        }
        else {
            /**
            * Niestety nie wiedzieć czemu ale get_class() nie wywala się gdy podamy tej funkcji np: null
            */
            if (!is_object($entity)) {

                if ($throwException)
                    throw new Exception('Entity is not an object, is: '.gettype($entity));
                return gettype($entity);
            }
            $class = get_class($entity);
        }

        return static::cleanClassNamespace($class);
    }    /**
    * Jeśli jest to klasa proxy to trzeba przerobić ścieżkę na właściwą klasę
    *
    * @param string $classNamespace
    * @return string
    */
    public static function cleanClassNamespace($classNamespace) {
        if ( strpos( $classNamespace, "\\" . preg_quote(ClassUtils::MARKER) . "\\" ) )
            $classNamespace = preg_replace('#^[^\\\\]+\\\\[^\\\\]+\\\\(.*)$#', '$1', $classNamespace);

        return $classNamespace;
    }
}