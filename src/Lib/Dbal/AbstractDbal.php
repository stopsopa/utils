<?php

namespace Stopsopa\UtilsBundle\Lib\Dbal;

use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Stopsopa\UtilsBundle\Lib\AbstractException;
use Stopsopa\UtilsBundle\Lib\Json\Json;
use Exception;
use PDO;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 *
    /* *
     * @return DbalLocations;
     * /
    public static function getDbalLocations() {
        $key = DbalLocations::SERVICE;
        if (!array_key_exists($key, static::$services))
            static::$services[$key] = new DbalLocations(static::$app);

        return static::$services[$key];
    }
 */
abstract class AbstractDbal
{
//    const SERVICE = 'dbal.speakers';
//    const TABLE   = 'speakers';
//    const PRIMARY = 'id'; // dobrze jest zdefiniować także primary key żeby orm nie szukał sam tego pola
    const WARM = '__warm';



    /**
     * @param mixed $data , true  - zwraca nazwę metody wzbogacajęcej dane,
     *                      null  - testuje czy jest metoda w klasie dziedziczącej,
     *                      array - wzbogaca dane przy tej konfiguracji brana jest pod uwagę flaga $many
     * @param bool $many - czy przetwarzam jedną encję czy tablicę encji
     *
     * @return string
     *
    protected function __warm(&$d)
    {
       .. działany na referencji
    }
    */
    /**
     * Sugerowana metoda create
     */
//    public function create() {
//        return array(
//            'created_at' => date('Y-m-d H:i:s'),
//            'updated_at' => date('Y-m-d H:i:s')
//        );
//    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder($symbol = 'e') {
        return AbstractApp::getDbal()->createQueryBuilder()->select("$symbol.*")->from(static::TABLE, $symbol);
    }
    /**
     * Odfiltrowuje podane dane tak aby w zwrotce były tylko dane dla kolumn które istniją w tabeli którą reprezentuje ta klasa modelu
     * @param type $data
     * @param type $existing - domyslnie zwraca te które istniją, można tą flagą odwrócić
     * @return type
     */
    public function filterDataToExistingInDb($data, $existing = true) {
        $table = static::TABLE;

        $columns = $this->getFields();

        $return = array();

        foreach ($data as $key => &$d) {
            if ( array_key_exists($key, $columns) === $existing) {
                $return[$key] = $d;
            }
        }

        return $return;
    }
    protected $primaryKey;
    public function getPrimaryKey() {

        if ($this->primaryKey === false) {
            return false;
        }

        if ($this->primaryKey) {
            return $this->primaryKey;
        }

        // w pierwszej kolejnosc sprawdzam czy jest w const
        if (defined(get_class($this).'::PRIMARY')) {
            return $this->primaryKey = static::PRIMARY;
        }

        // nastepnie szukam w bazie
        if (!$this->primaryKey) {
            foreach ($this->getFields() as $col => $d) {
                if ($d['Key'] === 'PRI') {
                    return $this->primaryKey = $col;
                }
            }
        }

        $table = static::TABLE;

        $this->primaryKey = false;

        return false;
    }
    public function persist($data) {
        $tabledata = $this->filterDataToExistingInDb($data);

        $dbal = AbstractApp::getDbal();

        if (empty($data[$this->getPrimaryKey()])) {
            $dbal->insert(static::TABLE, $tabledata);

            $id = $dbal->lastInsertId();
        }
        else {
            $id = $data[$this->getPrimaryKey()];

            $dbal->update(static::TABLE, $tabledata, array(
                $this->getPrimaryKey() => $id
            ));
        }

        return intval($id);
    }

    protected $columns;
    /**
     * Wyciąga informacje z bazy o strukturze tabli którą reprezentuje ta klasa modelu
     * Dodatkowo dane są cachowane
     * @param type $column
     * @return type
     */
    protected function getFields($column = null) {

        if (!$this->columns) {
            $table = static::TABLE;
            $this->columns = array();

            foreach (AbstractApp::getDbal()->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC) as $d) {
                $this->columns[$d['Field']] = $d;
            }
        }

        if ($column) {
            return $this->columns[$column];
        }

        return $this->columns;
    }

    /**
     *
     * @param array $data
     * @param array $types
     * @return affected rows
     */
    public function insert(array $data, array $types = array()) {

        if (method_exists($this, 'create')) {
            $data = array_merge($this->create(), $data);
        }

        $data = $this->filterDataToExistingInDb($data);

        $primary = $this->getPrimaryKey();

        if (array_key_exists($primary, $data)) {
            return $this->update($data, $data[$primary], $types, true);
        }

        return AbstractApp::getDbal()->insert(static::TABLE, $data, $types);
    }
    /**
     *
     * $orm->update(array(
     *   'name' => 'nazwa',
     *   'created_at' => '2015-09-03'
     * ), 456);
     *
     * lub
     *
     * $orm->update(array(
     *   'name' => 'nazwa',
     *   'created_at' => '2015-09-03'
     * ), array(
     *   'primary_key' => 345
     * ));
     *
     * lub
     *
     * $orm->update(array(
     *   'name' => 'nazwa',
     *   'created_at' => '2015-09-03'
     * ), array(
     *   'name' => 'nameto change',
     *   'created_at' => '2015-09-03'
     * ));
     *
     * @param array $data
     * @param type $identifier - id or array of criterias
     * @param array $types
     * @return type affected rows
     */
    public function update(array $data, $identifier, array $types = array(), $throw = false)
    {
        if (method_exists($this, 'create')) {
            $data = array_merge($this->create(), $data);
        }

        $data = $this->filterDataToExistingInDb($data);

        $primary = $this->getPrimaryKey();

        if (!empty($data[$primary])) {
            $id = $data[$primary];
        }

        if (is_numeric($identifier)) {
            $identifier = array(
                $primary => $identifier
            );
        }

        $affected = AbstractApp::getDbal()->update(static::TABLE, $data, $identifier, $types);

        if ($throw) {
            // sprawdzę czy tylko dane były takie same, czy to był faktyczny problem ze znalezieniem encji
            $pass = false;
            if (!$affected) {
                if ($this->findOneBy($identifier)) {
                    $pass = true;
                }
            }
            if (!$pass && !$affected) {
                throw new Exception("Update data ".json_encode($data)." by identifiers ".json_encode($identifier)." not maked any changes in table `".static::TABLE."` at all");
            }
        }

        return $affected;

//        $a = func_get_args();
//
//        if (count($a) < 2) {
//            throw new Exception("Zbyt mała liczba argumentów, oczekuje się: id - id wiersza w bazie, kolumna, nowa wartość lub id - id wiersza w bazie, tablica asocjacyjna wartości. Podano wartości: " . Json::encode($a));
//        }
//
//        $table = static::TABLE;
//
//        if (is_array($a[1])) {
//            $set = implode(', ', array_map(function ($key) {
//                return "`$key` = :$key";
//            }, array_keys($a[1])));
//            $query = "
//UPDATE  $table
//SET     $set
//WHERE   id = :id
//";
//            $stmt = AbstractApp::getDbal()->prepare($query);
//
//            foreach ($a[1] as $key => &$val) {
//                $stmt->bindValue($key, $val);
//            }
//
//            if (is_array($a[0]))
//                $a[0] = $a[0]['id'];
//
//            $stmt->bindValue('id', $a[0]);
//
//            return $stmt->execute();
//        }
//
//        if (count($a) > 2 && is_string($a[1])) {
//            return $this->update($a[0], [
//                $a[1] => $a[2]
//            ]);
//        }
//
//        throw new Exception("Nieprawidłowe użycie metody. " . Json::encode($a));
    }

    public function createHashIfEmptyForAll() {

        $list = $this->findBy(array(
            'hash' => null
        ));

        $primary = $this->getPrimaryKey();

        foreach ($list as &$l) {
            $this->getHash($l[$primary]);
        }

        return $this;
    }

    public function getHash($id)
    {
        $primary = $this->getPrimaryKey();

        $empl = AbstractApp::getDbal()->fetchAssoc('SELECT * FROM ' . static::TABLE . ' WHERE `'.$primary.'` = :id', array('id' => $id));

        if (!empty($empl['hash']))
            return $empl['hash'];

        $this->update($id, 'hash', $this->generateUniqueHash());

        return $this->getHash($id);
    }
    public static function getVarcharLength($field, $table = null, $dbal = 'default') {

        if (!$table) {
            $table = static::TABLE;
        }

        $length = AbstractApp::getDbal($dbal)->fetchColumn("
select        CHARACTER_MAXIMUM_LENGTH l
from          information_schema.columns
where         table_schema = DATABASE()
          AND table_name = '$table'
          AND COLUMN_NAME = '$field'
        ");

        if ($length === false) {
            return null;
        }

        return intval($length);
    }
    public function count()
    {
        $count = AbstractApp::getDbal()->fetchColumn("SELECT count(*) c FROM " . static::TABLE);

        if ($count === false) {
            return null;
        }

        return intval($count);
    }
    public function extend($data = false, $many = false)
    {
        $name = static::WARM;

        if ($data === true) {
            return method_exists(get_called_class(), $name);
        }

        if (is_array($data)) { // array
            if ($this->extend(true)) { // jest metoda i rozszerzamy
                if ($many) {
                    foreach ($data as &$d) {
                        $this->{$name}($d);
                    }

                    return $data;
                }

                // przepuszczenie przez metodę
                $this->{$name}($data);

                return $data;
            }
        }

        return $data;
    }

    public function find($id)
    {
        return $this->extend(AbstractApp::getDbal()->fetchAssoc('SELECT * FROM ' . static::TABLE . ' WHERE '.$this->getPrimaryKey().' = :id', [
            'id' => $id
        ]));
    }

    /**
     * @param array $criteria można podać teraz ['orderby' => 'name desc'], później jeśli zajdzie taka potrzeba to się dostawi inne
     *
     * @return type
     */
    public function findAll($criteria = [])
    {
        is_array($criteria) || ($criteria = []);
        $query = 'SELECT * FROM ' . static::TABLE;

        if (isset($criteria['orderby'])) {
            $query .= ' ORDER BY ' . $criteria['orderby'];
        }

        $stmt = AbstractApp::getDbal()->query($query);
        $list = [];

        $primary = $this->getPrimaryKey();

        if ($primary) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $list[$row[$primary]] = $row;
            }
        }
        else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $list[] = $row;
            }
        }

        return $this->extend($list, true);
    }

    protected function _bindConditionalQuery(&$stmt, &$conditions = [])
    {
        if (is_array($conditions)) {
            foreach ($conditions as $name => $data) {
                if ($data !== null) {
                    $stmt->bindValue($name, $data);
                }
            }
        }
    }

    protected function _buildConditionalQuery($conditions = [])
    {
        $query = "SELECT * FROM " . static::TABLE . " e";

        if (is_array($conditions)) {
            $i = 0;
            foreach ($conditions as $name => $data) {
                $query .= $i++ ? ' AND' : ' WHERE';
                if ($data === null) {
                    $query .= " e.$name is null";
                }
                else {
                    $query .= " e.$name = :$name";
                }
            }
        }

        return $query;
    }

    public function findOneBy($conditions = [])
    {
        $query = $this->_buildConditionalQuery($conditions);

        $query .= ' LIMIT 1';

        $stmt = AbstractApp::getDbal()->prepare($query);

        $this->_bindConditionalQuery($stmt, $conditions);

        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $this->extend($row, false);
        }

        return null;
    }

    public function findBy($conditions = [])
    {
        $query = $this->_buildConditionalQuery($conditions);

        $stmt = AbstractApp::getDbal()->prepare($query);

        $this->_bindConditionalQuery($stmt, $conditions);

        $stmt->execute();

        return $this->extend($stmt->fetchAll(PDO::FETCH_ASSOC), true);
    }

    public function findOrThrow($id)
    {
        $row = $this->find($id);

        if ($row)
            return $row;

        $table = static::TABLE;
        throw new NotFoundHttpException("Nie odnaleziono encji '$table', id: '$id'");
    }

    public function findOrThrowBy($conditions = [])
    {
        $rows = $this->findBy($conditions);

        if (count($rows)) {
            return $rows;
        }

        $table = static::TABLE;
        throw new NotFoundHttpException("Nie odnaleziono encji '$table', kryteria: " . Json::encode($conditions));
    }

    public function findOrThrowOneBy($conditions = [])
    {
        $row = $this->findOneBy($conditions);

        if ($row) {
            return $row;
        }

        $table = static::TABLE;
        throw new NotFoundHttpException("Nie odnaleziono encji '$table', kryteria: " . Json::encode($conditions));
    }

    protected $dynamicmethods = ['findOneBy', 'findBy', 'findOrThrowOneBy', 'findOrThrowBy'];

    public function __call($method, $arguments)
    {
        foreach ($this->dynamicmethods as $check) {
            if (strpos($method, $check) === 0) {
                $param = lcfirst(substr($method, strlen($check)));

                return $this->{$check}([
                    $param => $arguments[0]
                ]);
            }
        }

        throw new Exception("Can't handle method '$method' in __call logic or you called wrong method");
    }
    protected function _micro()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }
    public function generateUniqueHash() {

        $table = static::TABLE;
        $stmt = AbstractApp::getDbal()->prepare("SELECT * FROM $table e WHERE e.hash = :hash");

        do {
            $stmt->bindValue('hash', $hash = md5(uniqid() . $this->_micro() . ' saltword'));
            $stmt->execute();
        } while ($stmt->fetch(PDO::FETCH_ASSOC));

        return $hash;
    }
    public function delete($identifier, $types = array()) {
        return AbstractApp::getDbal()->delete(static::TABLE, $identifier, $types);
    }
    public function getAutoincrementNextValue() {
//        SELECT `AUTO_INCREMENT`
//FROM  INFORMATION_SCHEMA.TABLES
//WHERE TABLE_SCHEMA = 'area_test_absolvent_pl'
//        AND   TABLE_NAME   = 'cities';
    }
}
