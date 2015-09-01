<?php

namespace Stopsopa\UtilsBundle\Lib\Dbals;

use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Stopsopa\UtilsBundle\Lib\Json\Json;
use Exception;
use PDO;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractDbal
{
    const WARM = '__warm';

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder($symbol = 'e') {
        return AbstractApp::getDbal()->createQueryBuilder()->select("$symbol.*")->from(static::TABLE, $symbol);
    }

    /**
     *
     * @param array $data
     * @param array $types
     * @return AbstractDbal
     */
    public function insert(array $data, array $types = array()) {
        AbstractApp::getDbal()->insert(static::TABLE, $data, $types);
        return $this;
    }

    /**
     * @param int $id - id encji
     * @param array $data - dane które mają być zmeinione
     * do przerobienia na zwykłą metodę dbal
     *
     * użycie:
     *   jeśli jedno pole:
     *    ->update(id, nazwa_kolumny, wartosc)
     *   jesli wiele pól:
     *    ->update(id, tablicaasocjacyjna_nazwa_pola_wartość)
     */
    public function update()
    {
        $a = func_get_args();

        if (count($a) < 2) {
            throw new Exception("Zbyt mała liczba argumentów, oczekuje się: id - id wiersza w bazie, kolumna, nowa wartość lub id - id wiersza w bazie, tablica asocjacyjna wartości. Podano wartości: " . Json::encode($a));
        }

        $table = static::TABLE;

        if (is_array($a[1])) {
            $set = implode(', ', array_map(function ($key) {
                return "`$key` = :$key";
            }, array_keys($a[1])));
            $query = "
UPDATE  $table
SET     $set
WHERE   id = :id
";
            $stmt = AbstractApp::getDbal()->prepare($query);

            foreach ($a[1] as $key => &$val) {
                $stmt->bindValue($key, $val);
            }

            if (is_array($a[0]))
                $a[0] = $a[0]['id'];

            $stmt->bindValue('id', $a[0]);

            return $stmt->execute();
        }

        if (count($a) > 2 && is_string($a[1])) {
            return $this->update($a[0], [
                $a[1] => $a[2]
            ]);
        }

        throw new Exception("Nieprawidłowe użycie metody. " . Json::encode($a));
    }

    public function createHashIfEmptyForAll() {
        $list = $this->findBy(array(
            'hash' => null
        ));
        foreach ($list as &$l) {
            $this->getHash($l['id']);
        }
        return $this;
    }

    public function getHash($id)
    {
        $empl = AbstractApp::getDbal()->fetchAssoc('SELECT * FROM ' . static::TABLE . ' WHERE id = :id', ['id' => $id]);

        if (!empty($empl['hash']))
            return $empl['hash'];

        $this->update($id, 'hash', $this->generateUniqueHash());

        return $this->getHash($id);
    }
    public function count()
    {
        $stmt = AbstractApp::getDbal()->query("SELECT count(*) c FROM " . static::TABLE);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return (int)$row['c'];
        }

        return 0;
    }

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
        return $this->extend(AbstractApp::getDbal()->fetchAssoc('SELECT * FROM ' . static::TABLE . ' WHERE id = :id', [
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

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[$row['id']] = $row;
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
    public function generateUniqueHash()
    {
        $table = static::TABLE;
        $stmt = AbstractApp::getDbal()->prepare("SELECT * FROM $table e WHERE e.hash = :hash");

        do {
            $stmt->bindValue('hash', $hash = md5(uniqid() . $this->_micro() . ' saltword'));
            $stmt->execute();
        } while ($stmt->fetch(PDO::FETCH_ASSOC));

        return $hash;
    }
}
