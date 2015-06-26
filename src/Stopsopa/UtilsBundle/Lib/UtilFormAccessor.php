<?php


namespace Stopsopa\UtilsBundle\Lib;

use ArrayAccess;
use Stopsopa\UtilsBundle\Lib\Exception\UtilArrayException;
use Stopsopa\UtilsBundle\Lib\Exception\UtilFormAccessorException;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilArray;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Form\Form;


class UtilFormAccessor {
    /**
     * @param Form $form
     * @param string $path
     * @return Form
     */
    public static function &getForm(Form &$form, $path) {

        $keys = array_filter(preg_split('#[\.\[\]]+#', $path), function ($val) {
            return $val;
        });

        $prefix = substr($path, 0, strlen($form->getName()) + 1);

        if (
            $form->getName().'[' === $prefix
        ||
            $form->getName().'.' === $prefix
        ) {
            array_shift($keys);
        }

        $val = $form;

        foreach ($keys as $k) {
            $val = static::_get($val, $k);
        }

        /* @var $val Form */
        return $val;
    }
    public static function getValue(Form &$form, $path) {

        $tmp = static::getForm($form, $path);

        return $tmp->getData();
    }
    public static function setValue(Form &$form, $path, $data) {

        $tmp = static::getForm($form, $path);

        $tmp->setData($data);

        return $tmp;
    }

    /**
     * @var PropertyAccessor
     */
    protected static $accessor;

    protected static function &accessorGet(&$o, $key) {

        if (!static::$accessor) {
            static::$accessor = PropertyAccess::createPropertyAccessor();
        }

        return static::$accessor->getValue($o, $key);
    }

    protected static function &_get(&$o, $p) {
        $result;

        if (static::_isArrayAccessable($o)) {
            try {
                if (UtilArray::offsetExists($o, $p)) {
                    $result = &$o[$p];
                    return $result;
                }
            } catch (UtilArrayException $ex) {
                nieginie($o);
                nieginie($p);
                throw new UtilFormAccessorException($ex->getMessage());
            }
        }

        $result = static::accessorGet($o, $p);
        return $result;
    }
    protected static function _isArrayAccessable ($object) {
      return is_array($object) || $object instanceof ArrayAccess;
    }
}