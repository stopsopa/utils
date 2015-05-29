<?php

namespace Stopsopa\UtilsBundle\Lib\Dubmpers;

use Cms\BaseBundle\Entity\AbstractEntity;
use Cms\BaseBundle\Entity\AbstractEntityException;
use Cms\BaseBundle\Lib\App;
use Cms\BaseBundle\Lib\Dubmpers\DumpToArrayInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * class Lib\Dumpers\KartaDumper extends AbstractDumperToArray {}
 * Cms\BaseBundle\Lib\Dubmpers\AbstractDumperToArray
 */
class AbstractDumperToArray {

    /**
     * Idziemy mocno na skróty
     * @param mixed $object
     * @param string $method
     * @return array
     */
    public static function dump($object, $method = null) {
        return App::getServiceDumper()->dump($object, static::getClassName(), $method);
    }

        /**
   * Uwaga, tam gdzie jest relacje oneToMany lub ManyToMany trzeba zwrócić pustą arraykę,
   * z kolei jak jest oneToOne lub ManyToOne to zwracamy normalnie null:
   *
      $data = $this->objToArray($event, array(
        'Id'             => 'id',
        'Title'          => array('name',''),
        'Description'    => array('description',''),
        'EventOrganizer' => array('organizator',''),
        'Address'        => array('address',''),
        'Phone'          => array('phone',''),
        'Details'        => array('details',array()),      <- zwracamy pustą tablice zamiast null
        'Gallery'        => 'gallery'                      <- zwracamy normalnie null
      ));
   * dodatkowa uwaga dla zwracania danych dla parserwów .NET:
   * lepiej jest dumpować powtarzające się dane tak:
    "Messages":[
      {
        "Message":"przejazd lini\u0105 6"
      },
      {
        "Message":"godzina odjazdu: 24:51"
      },
      {
        "Message":"godzina przyjazdu: 25:07"
      }
    ]
   * a nie tak:
    "Messages":[
      "przejazd lini\u0105 6",
      "godzina odjazdu: 24:51",
      "godzina przyjazdu: 25:07"
    ]
   * ... daltego że jest wtedy rzutowanie wprost obiektu json na obiekt w c#, nie trzeba kombinować specjalnie
   *
   * Główna metoda robocza, tutaj zaczyna się rekurencja:
   * $data = $this->get(DumperService::SERVICE)->dump($data, AppDumper::DUMPER);
   * $data = $this->get(DumperService::SERVICE)->dump($data, array(...)); -- format ->objToArray()
   * $data = $this->get(DumperService::SERVICE)->dump($data, AppDumper::DUMPER,'_dumpTours'); -- zaczynamy rzutowanie od konkretnej metody
   *
   * @param DumpToArrayInterface $object
   * @param boolean $throwException (def: true)
   * @param string $method
   * @return array
   * @throws Exception
   */
  public function dumpObject($object, $throwException = true, $method = null) {
    if (!is_array($object) && !is_object($object))
      return $this->_dumpPrimitives($object);

    if (AbstractEntity::isForeachable($object))
      return $this->_dumpCollection($object, $method);

    try {
      $dumpmethod = $this->_getMethodName($object);
      if (!$method && method_exists($this, $dumpmethod))
        return $this->$dumpmethod($object);

      if ($object instanceof DumpToArrayInterface)
        return $object->dumpToArray($scope = null);

      if ($method) {
        if (method_exists($this, $method))
          return $this->$method($object);
        else
          throw new Exception("In class '".AbstractEntity::getClassNamespace($this, $throwException = false)."' doesn't exist method '$method'");
      }
    }
    catch (DumperContinueException $e) {

      if ($throwException)
        throw $e;

      return null;
    }

    $class = AbstractEntity::getClassNamespace($object, $throwException = false);
    throw new Exception("Dumping object of class '$class' is not handled by dumper, you should implement interface 'DumpToArrayInterface' or declare method ".get_class($this)."->$dumpmethod.");
  }
  protected function _dumpCollection($collection, $method = null) {
    $data = array();
    foreach ($collection as $d) {
      try {
        if ($method) {
          if (method_exists($this, $method))
            $data[] = $this->$method($d);
          else
            throw new Exception("In class '".get_class($this)."' doesn't exist method '$method'");
        }
        else {
          $data[] = $this->dumpObject($d);
        }
      }
      catch (DumperContinueException $e) {
      }
    }

    if (!count($data))
      return array(); // faktycznie tutaj powinien być pusty array
//      return new stdClass();

    return $data;
  }
  public static function getClassName() {
    return get_called_class();
  }

  /**
   * Tutaj można sobie nadpisać metodę zwracania prymitywnych typów, czyli wszystkich poza object i array
   * @param mixed $data
   * @return mixed
   */
  protected function _dumpPrimitives($data) {
    return $data;
  }

  protected function _getMethodName($object) {
    $parts = explode('\\', AbstractEntity::getClassNamespace($object, false));
    $last  = array_pop($parts);
    $parts = implode('', $parts);
    return '_dump'.ucfirst("{$parts}_{$last}");
  }

  protected function _dump_DateTime($object) {
    return $object->format('Y-m-d H:i:s');
  }
  /**
   *
    $data = $this->objToArray($file, array(
      'FileId'           => 'id',
      'Name'             => 'name',
      'Mime'             => 'mime',
      'DbLink'           => 'path',
      'date'             => array('date','domyślna data') - normalnie jest null dlatego można tutaj podać coś innego co ma być domyślnie
    ));
   * @param object $object
   * @param array $fields
   * @return array
   */
  public function objToArray($object, $fields) {
    $data = array();
    foreach ($fields as $target => $key) {

      $default = null;
      if (is_array($key)) {
        $default = $key[1];
        $key     = $key[0];

        // biblioteka rzuca exception tylko gdy nie znajduje wartości, jeśli natomiast naturalnie zwracany jest null to muszę go ręcznie sprawdzić
        try {
          $data[$target] = $this->dumpObject(AbstractEntity::getValueByMethodOrAttribute($object, $key));

//          if (is_null($data[$target]))
          if (!$data[$target])
            $data[$target] = $default;
        }
        catch (AbstractEntityException $e) {
          $data[$target] = $default;
        }
        catch (DumperContinueException $e) {
          $data[$target] = $default;
        }
      }
      else {
        $data[$target] = $this->dumpObject(AbstractEntity::getValueByMethodOrAttribute($object, $key, $default), $throwException = false);
      }

    }
    return $data;
  }
  /**
   * @var ContainerInterface
   */
  protected $container;

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }
}