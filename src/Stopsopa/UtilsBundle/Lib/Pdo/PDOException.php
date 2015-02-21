<?php

namespace Stopsopa\UtilsBundle\Lib\Pdo;
use PDOException;

/**
 * @author Szymon Działowski
 */
class PDOException extends PDOException {
    const CODEEXECUTION = 1; // błąd podczas wykonania zapytania w bazie
    const CODENOTFOUND  = 2; // brak szukanych danych    
    const CODEBIND      = 3; // brak bindowania    
}
