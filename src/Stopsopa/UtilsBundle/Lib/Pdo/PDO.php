<?php

namespace Stopsopa\UtilsBundle\Lib\Pdo;

use PDO as NativePDO;
use PDOStatement as NativeStatement;
use Exception;

/**
 * @author Szymon Działowski
 *
 * Tutaj można rozszerzać natywny obiekt PDO
 * Przykład użycia klasy:
 *
 *
 *
 *
 * use Site\PDOException;
 *
 *             try {
 *                 $list = App::getPdoUser()->prepare("
 * select               *
 * from                 cases
 * where                id > :id
 * ")
 *                 ->setParam('id', 3, PDO::PARAM_INT)
 *                 ->getResult()
 *                 ->fetchAllIdArray('idd');
 *             } catch (PDOException $ex) {
 *                 if ($ex->getCode() == PDOException::CODENOTFOUND) {
 *                     die('Odwołanie do nieistniejących danych');
 *                 }
 *                 throw $ex;
 *             } catch (PDOException $ex) {
 *                 throw $ex;
 *             }
 *             } catch (Exception $ex) {
 *                 throw $ex;
 *             }
 *
 *
 *  Lista kodów błędów sql server: http://www.sql-server-helper.com/error-messages/msg-1-500.aspx
 */
class PDO extends NativePDO
{
    /**
     * Tutaj będzie przechowywany dsn.
     *
     * @var type
     */
    protected $driver;

    /**
     *Tutaj będzie przechowywany typ bazy danych.
     *
     * @var type
     */
    protected $dbkind;

    /**
     * http://www.php.net/manual/en/ref.pdo-dblib.connection.php
     * mssql:host=localhost;dbname=testdb
     * sybase:host=localhost;dbname=testdb
     * dblib:host=localhost;dbname=testdb.
     *
     * @param type $dsn
     * @param type $username
     * @param type $passwd
     * @param type $options
     *
     * @return \Site\PDO
     */
    public function __construct($dsn, $username = null, $passwd = null, $options = array())
    {
        parent::__construct($dsn, $username, $passwd, $options);

        $this->_detectDb($dsn);

        $this->setAttribute(NativePDO::ATTR_ERRMODE, NativePDO::ERRMODE_EXCEPTION);

        try {
            $this->setAttribute(NativePDO::ATTR_TIMEOUT, 2);
        } catch (Exception $ex) {
        }

        return $this;
    }
    protected function _detectDb($dsn)
    {
        $parts = explode(':', $dsn);
        $t = $this->driver = strtolower($parts[0]);

    // na razie detekcję zrobimy taką
    $this->dbkind = 'mysql';

        if (in_array($t, explode('|', 'mssql|sybase|dblib'))) {
            $this->dbkind = 'mssql';
        }
    }

    /**
     * Wykonuje DECLARE @ROWS INT = :rows, @PAGE INT = :page
     * przed zapytaniami mssql
     * Chciałem nazwać declare ale nie można bo to jest zastrzeżone słowo w php.
     *
     * Użycie:
     *    // typy zmiennych: http://msdn.microsoft.com/en-us/library/ms187752.aspx
     * <pre>
     *    $pdo->declar(array(
     *		'ROWS INT' => 10,
     *	        'ROWS NVARCHAR => 'TEST'
     *    ));
     * LUB JEŚLI TYLIKO JEDEN PARAMETR:
     *    $pdo->declar('ROWS INT', 10);
     * </pre>
     *
     * Można później dorobić ustawianie PDO::PARAM_INT na podstawie drugiej części deklaracji @ROWS INT
     * idzie do kosza.. :/
     */
//    public function declar($params, $arg2 = null) {
//	if ($this->dbkind != 'mssql')
//	    throw new Exception("Db kind must be mssql, is: '".$this->dbkind."'");
//
//	if (is_string($params)) {
//	    $params = array(
//		$params => $arg2
//	    );
//	}
//
//	$list = array();
//	$i = 1;
//	$sql = array();
//	foreach ($params as $name => &$v) {
//	    $sqlid = "param_$i";
//	    $list[$sqlid] = $v;
//	    $sql[] = "@$name = :$sqlid";
//	    ++$i;
//	}
//	niechginiee($list);
//	niechginiee($sql);
//
//	$stmt = $this->prepare('DECLARE '.  implode(', ', $sql));
//
//	foreach ($list as $sqlid => &$d) {
//	    $stmt->bindValue($sqlid, $d);
//	}
//	$stmt->execute();
//
//	return $this;
//    }

    public function prepare($string, $driver_options = array())
    {
        return $this->_decorate(parent::prepare($string, $driver_options));
    }
    public function query($string, $parameter_type = NativePDO::PARAM_STR)
    {
        return $this->_decorate(parent::query($string, $parameter_type));
    }
    /**
     * @param NativeStatement $stmt
     *
     * @return PDOStatement
     */
    protected function _decorate($stmt)
    {
        if ($stmt instanceof NativeStatement) {
            return new PDOStatement($stmt);
        }

        return $stmt;
    }
}
// die;
