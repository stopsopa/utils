<?php

namespace Stopsopa\UtilsBundle\Lib\Pdo;

use PDOStatement as NativePDOStatement;
use PDO as NativePDO;
use Exception;

/**
 * @author Szymon Działowski
 * @see NativePDOStatement
 * Tutaj można rozszerzać natywny obiekt PDOStatement
 */
class PDOStatement {
    /**
     * The NativePDOStatement we decorate
     */
    protected $stmt;
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
    }
    /**
     * Działa tak jak execute z tym że zwraca obiekt stmt dla dalszych wywołań metod kaskadowo, np:
     * 
     * $list = App::getPdo()->prepare('sql :id')->getResult()->setParam('id', PDO::PARAM_INT)->fetchAllIdArray();
     * 
     * .. nie nadpisywałem samej metody ->execute() dla zachowania wstecznej zgodności.
     * nazwa getResult() pochodzi z dolnej warstwy systemu ORM Doctrine 2, patrz np: http://doctrine-dbal.readthedocs.org/en/latest/reference/security.html?highlight=getResult#right-prepared-statements
     * @param array $input_parameters
     * @throws PDOException
     */
    public function getResult(array $input_parameters = null) {
        
        if (!$this->execute($input_parameters)) 
            throw new PDOException("Błąd podczas wykonania zapytania: \n--------\n".$stmt->queryString."\n--------\n", PDOException::CODEEXECUTION);
        
        return $this->_decorate($this);
    }

    /**
     * Przepakowuje dane do tablicy tak aby kluczem był id z tabeli a reszta (także z kluczem) w wartości
     * @param type $id
     * @return PDOStatement
     * @throws PDOException
     */
    public function &fetchAllIdArray($id = null) {
        $list = array();
        
        while ($row = $this->fetch(PDO::FETCH_ASSOC)) {
            
            if ($id) {
                if (!isset($row[$id])) 
                    throw new PDOException("Zwrócone wartości nie mają klucza '$id', przykład ".  json_encode($row), PDOException::CODENOTFOUND);

                $list[$row[$id]] = $row;                
            }
            else {
                $list[] = $row;
            }
        }
        
        return $list;
    }
    /**
     * Działa tak samo jak bindValue z tym że zwraca obiekt statement dla wywołań kaskadowych, patrz przykład w komentarzu dla metody tej klasy ->getResult()
     * @param type $id
     * @param type $value
     * @param type $data_type
     * @return PDOStatement
     * @throws PDOException
     */
    public function setParam($id, $value, $data_type = NativePDO::PARAM_STR) {
	
        if (!$this->bindValue($id, $value, $data_type)) 
            throw new PDOException("Błąd podczas bindowania parametru '$id' o wartości '$value'", PDOException::CODEBIND);
        
        return $this;        
    }
    public function getCount($id = 'c') {        
        if ($row = $this->fetch(PDO::FETCH_ASSOC)) {
            
            if ($id) {
                if (!isset($row[$id])) 
                    throw new PDOException("Err 0: Zwrócone wartości nie mają klucza '$id', przykład ".  json_encode($row), PDOException::CODENOTFOUND);

                return (int)$row[$id];
            }
            else {
                throw new PDOException("Nieprawidłowy parametr id : '$id'", PDOException::CODENOTFOUND);
            }
        }
        
        throw new PDOException("Nie było możliwe pobranie '$id' z rezultatu zapytania", PDOException::CODENOTFOUND);
    }

    /**
     * @param NativeStatement $stmt
     * @return PDOStatement
     */
    protected function _decorate($stmt) {
	
	if ($stmt instanceof NativeStatement) 
	    return new PDOStatement($stmt);
	
	return $stmt;
    }
    
    /**
     * @param string $function_name
     * @param array $parameters arguments
     * @return PDOStatement
     * @throws Exception
     */
    function __call($method, $args) {
	
        if (!method_exists($this->stmt, $method)) 
            throw new Exception("Call to undefined method ".get_class($this->stmt)."::$method");

        return call_user_func_array(array($this->stmt, $method), $args);
    }    
}