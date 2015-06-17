<?php

namespace Stopsopa\UtilsBundle\Lib\Standalone;
use Symfony\Component\Security\Core\Util\ClassUtils;
use Stopsopa\UtilsBundle\Lib\Exception\UtilNestedException;

class UtilNested {
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
     * @param string|array $key - klucz w fromacie 'comment' lub z kropką typu 'author.id'
     * @param $default - gdy nie ma takiego propertisa to a podamy ten parametr to nie rzuci exception tylko zwróci tą wartość
     * @return mixed
     * @throws Exception
     *   codes:
     *     ($e->getCode()%100)==1 - Parameter 'attr'  is not a string, is:
     *     ($e->getCode()%100)==2 - Parameter 'attr' is empty string
     *     ($e->getCode()%100)==3 - Key '$attr' doesn't exist in array
     */
    public static function get($entity, $key) {
        $args = func_get_args();
        $isdefault = (count($args) > 2);

        if (!is_string($key))
            throw new UtilNestedException("Parameter 'attr'  is not a string, is: " . gettype($key), 1);

        $key = trim($key);

        if ($key === '')
            throw new UtilNestedException("Parameter 'attr' is empty string", 2);

        if ( strpos($key, '.') !== false ) {
            try {
                foreach (self::cascadeExplode($key) as $k)
                    $entity = self::get($entity, $k);

                return $entity;
            }
            catch (UtilNestedException $e) {
                if ($e->getCode() < 100) {

                    if ($isdefault)
                        return $args[2];

                    throw new UtilNestedException($e->getMessage().", recursion key: '$key'", (100 + $e->getCode()));
                }
                throw $e;
            }
        }

        $short = ucfirst($key);

        if (is_array($entity)) {

            if (array_key_exists($key, $entity))
                return $entity[$key];
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
                if ($entity->offsetExists($key))
                    return $entity[$key];
            }
            if (strpos($key, '(') !== false) {
                $key = rtrim($key, '()');
                if (method_exists($entity, $key))
                    return $entity->$key();
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

                if (property_exists($entity, $key))
                    return $entity->$key;
            }
        }

        if ($isdefault)
            return $args[2];

        $class = self::getClassNamespace($entity, $throwException = false);
        throw new UtilNestedException(__METHOD__." error: Methods get$short(), is$short(), has$short(), property '$key' or method '$key()' doesn't exist in class $class", 4);
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
    }
    /**
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
     * @param string $key
     * @param mix    $val
     * Nic z tego nie będzie bo nie można się dostać do arrayki która jest private/protected składową obiektu przez referencję :/ koniec krpoka
     * @return boolean
     */
//    public static function set(&$source, $key, $val)
//    {
//        if ($key) {
//            return false;
//        }
//
//        $key = static::cascadeExplode($key);
//
//        $element = &$source;
//        while (($d = array_shift($key)) !== null) {
//            if (count($key)) {
//                if (! (isset($element[$d]) && is_array($element[$d]))) {
//                    $element[$d] = array();
//                }
//
//                $element = &$element[$d];
//            } else {
//                $element[$d] = $val;
//
//                return true;
//            }
//        }
//
//        return true;
//    }
    public static function cascadeExplode($key)
    {
        $key = preg_split("#(?<!\\\\)\.#", $key);

        foreach ($key as $k => &$d) {
            $d = str_replace('\\.', '.', $d);
        }

        return $key;
    }
}