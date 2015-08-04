<?php

namespace Stopsopa\UtilsBundle\Services;

use App;
use Lib\Json;
use PDO;

/**
 * Stopsopa\UtilsBundle\Services\LogService.
 */
class LogService
{
    protected $pdo;
    /**
     * @return Pdo
     */
    protected function _pdo()
    {
        $this->pdo or ($this->pdo = App::getPdo('log'));

        return $this->pdo;
    }
    public function clearDb()
    {
        $file = App::getConfig('pdo.log.db');

        if (file_exists($file)) {
            unlink($file);
        }

        file_put_contents($file, '');

        return $this;
    }
    public function log($error, $data = null)
    {
        is_string($data) or ($data = Json::encode($data));
        $stmt = $this->_pdo()->prepare('INSERT INTO "Logs" ("time", "error", "data") VALUES (:time, :error, :data)');
        $stmt->bindValue('time', date('Y-m-d H:i:s'));
        $stmt->bindValue('error', $error);
        $stmt->bindValue('data', $data);
        $stmt->execute();

        return $this;
    }
    public function count()
    {
        $stmt = $this->_pdo()->query('SELECT count(*) c FROM Logs');

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return (int) $row['c'];
        }

        return;
    }
    public function findNewest($field = null)
    {
        $stmt = $this->_pdo()->query('SELECT * FROM Logs ORDER BY time DESC LIMIT 1');

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($field) {
                return $row[$field];
            }

            return $row;
        }

        return;
    }
    public function agregateLogs($error, $data = null)
    {
        App::getServiceLog()->log($error, $data);

        $subject = 'Message subject ąśżźćę';
        $body = App::template('stopsupervisor', array(
            'error' => $error,
            'data' => $data,
            'host' => getHost(),
        ));

        $message = Swift_Message::newInstance()
            ->setSubject('webservice ['.getHost().'] - error')
            ->setFrom('noreplay@pcube.com', 'webservice')
            ->setTo(App::getConfig('swiftmailer.cron_log_errors'))
            ->setBody($body, 'text/html')
            ->addPart(strip_tags($body), 'text/plain')
        ;
        App::getServiceMailer()->send($message);
    }
    public function buildTables()
    {
        $pdo = $this->clearDb();
        $pdo->_pdo()->query('
CREATE TABLE Logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT, 
  time DATETIME,
  error TEXT,
  data TEXT
);  
');
    }
}
