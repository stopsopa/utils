<?php

namespace Cms\BaseBundle\Entity;

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

    const DATE_FORMAT = 'Y-m-d';
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    const DATE_TIME_FORMAT_SHORT = 'Y-m-d H:i';
    const TIME_FORMAT = 'H:i:s';

    /**
     * UNIFIKACJA STATUSÓW W ENCJACH
     */
    const S_NEW       = 'new';
    const S_ACCEPTED  = 'accepted';
    const S_STOPED    = 'stopped';
    const S_DELETED   = 'deleted';
    const S_ARCHIVED  = 'archived';
    const S_PRIVATE   = 'private';
    
    /**
     * Dla obsługi eventów doctrine2 livecycle events
     * @var boolean 
     */
    public $_lcet;

    public static function getStatusList() {
        return array(
            self::S_NEW        => 'Nowy',
            self::S_ACCEPTED   => 'Zaakceptowany',
            self::S_STOPED     => 'Wstrzymany',
            self::S_DELETED    => 'Usunięty',
            self::S_ARCHIVED   => 'Archiwalny'
        );
    }
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
     * Metoda testuję podaną klasę po kolei (gdy w 'attr' podamy dla przykładu "test"):
     * - czy istnieje metoda getTest() lub isTest() lub atrybut test
     * @param object $entity
     * @param string $attr
     * @return true
     * @throws Exception gdy nic nie znalazł
     */
    public static function checkValueByMethodOrAttribute($entity, $attr) {
      try {
        self::getValueByMethodOrAttribute($entity, $attr);
        return true;
      }
      catch (AbstractEntityException $e) {
        return false;
      }
    }
    
    /**
     * Metoda testuję podaną klasę po kolei (gdy w 'attr' podamy dla przykładu "test"):
     * - czy jest to array 
     *   - czy istnieje w niej taki klucz, jeśli tak to go zwróci (wchodzi wgłąb arrayki po kluczach oddzielonych kropką)
     * - czy jest to obiekt:
     *   - czy istnieje metoda getTest(), jeśli tak to wykonue ją na obiekcie $entity i zwraca rezultat
     *   - czy istnieje metoda isTest(), jeśli tak to wykonue ją na obiekcie $entity i zwraca rezultat
     *   - czy istnieje atrybut klasy 'test', jeśli tak to go zwraca
     * - rzuca exception lub zwraca domyślną wartość jeśli została ustawiona (trzeci parametr $default)
     * @param object $entity
     * @param string|array $attr - klucz w fromacie 'comment' lub z kropką typu 'author.id'
     * @param $default - gdy nie ma takiego propertisa to a podamy ten parametr to nie rzuci exception tylko zwróci tą wartość
     * @return mixed
     * @throws Exception
     *   codes:
     *     ($e->getCode()%100)==1 - Parameter 'attr'  is not a string, is: 
     *     ($e->getCode()%100)==2 - Parameter 'attr' is empty string
     *     ($e->getCode()%100)==3 - Key '$attr' doesn't exist in array
     */
    public static function getValueByMethodOrAttribute($entity, $attr) {       
        $args = func_get_args();
        $isdefault = (count($args) > 2);
        
        if (!is_string($attr)) 
          throw new AbstractEntityException("Parameter 'attr'  is not a string, is: ".  gettype($attr), 1); 
        
        $attr = trim($attr);

        if ($attr === '') 
          throw new AbstractEntityException("Parameter 'attr' is empty string", 2);        
        
        if ( strpos($attr, '.') !== false ) {
          try {            
            foreach (explode('.',$attr) as $k)
              $entity = self::getValueByMethodOrAttribute($entity, $k);              
            
            return $entity;
          }
          catch (AbstractEntityException $e) {
            if ($e->getCode() < 100) {   
            
              if ($isdefault) 
                return $args[2];
              
              throw new AbstractEntityException($e->getMessage().", recursion key: '$attr'", (100+$e->getCode()));            
            }
            throw $e;
          }            
        } 
        
        $short = ucfirst($attr);
        
        if (is_array($entity)) { 
          
          if (array_key_exists($attr, $entity)) 
            return $entity[$attr];
          
        }
        if (is_object($entity)) {          
          if ($entity instanceof ArrayAccess) {
            /**
             * Uwaga !!!: array_key_exist nie jest kompatybilne z uzyciem interfejsu SPL ArrayAccess
             * dlatego jeżli w środku trafi się obiekty typu Doctrine\Common\Collections\ArrayCollection lub Doctrine\ORM\PersistentCollection
             * lub inny implementujący ArrayAccess intefejs to będzie problem
             * ... niżej wsadziłem inne rozwiązanie (niestety nieoptymalne ale za to działające)
             */
  //          if (array_key_exists($attr, $entity)) 
  //            return $entity[$attr];
            if ($entity->offsetExists($attr)) 
              return $entity[$attr];          
          }
          if (strpos($attr, '(') !== false) {
            $attr = rtrim($attr, '()');
            if (method_exists($entity, $attr))
              return $entity->$attr();            
          }
          else {
            $method = "get$short";
            if (method_exists($entity, $method))
              return $entity->$method();

            $method = "is$short";
            if (method_exists($entity, $method))
              return $entity->$method();

            $method = "has$short";
            if (method_exists($entity, $method))
              return $entity->$method();

            if (property_exists($entity, $attr))
              return $entity->$attr;
          }
        }  
        
        if ($isdefault) 
          return $args[2]; 

        $class = self::getClassNamespace($entity, $throwException = false);
        throw new AbstractEntityException(__METHOD__." error: Methods get$short(), is$short(), has$short(), property '$attr' or method '$attr()' doesn't exist in class $class", 4);
    }
    /**
     * Sprawdza czy obiekt można przejść foreach 
     * @param ArrayAccess $object
     * @return boolean
     */
    public static function isForeachable($object) {
      return is_array($object) || ( is_object($object) && $object instanceof Traversable);
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
    

    /**
     * Zwraca datę utworzenia encji w formacie zadanym w stałej DATE_TIME_FORMAT jeżeli istnieje pole created
     * 
     * @return string
     */
    public function getFormatedCreated() {
        return property_exists($this, 'created') && $this->created instanceof DateTime ? $this->created->format(self::DATE_TIME_FORMAT) : '';
    }

    /**
     * Zwraca datę aktualizacji encji w formacie zadanym w stałej DATE_TIME_FORMAT jeżeli istnieje pole update
     * @return string
     */
    public function getFormatedUpdated() {
        return property_exists($this, 'updated') && $this->updated instanceof DateTime ? $this->updated->format(self::DATE_TIME_FORMAT) : '';
    }
    /**
     * Zwraca obiekt usera, który jest twórca danej encji. 
     * 
     * @return \FOS\UserBundle\Entity\User
     */
    public function getCreator() {
        return null;
    }

}