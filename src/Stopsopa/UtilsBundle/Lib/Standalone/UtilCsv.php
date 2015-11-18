<?php

namespace Stopsopa\UtilsBundle\Lib\Standalone;

class UtilCsv
{
    public static $isbuildin;


    /**
       Kompletny Przykład użycia:


     @-Route("/employer/{id}", name="surveys.employer")

    public function employersAction (Request $request, $id) {

        $man = $this->get(CityManager::SERVICE);

        $city = $man->findOrThrow($id);

        $data = '';

        $dbal = AbstractApp::getDbal();

        $stmt = $dbal->prepare("
SELECT              e.id eid, e.name, es.*
FROM                employer_survey es
         INNER JOIN employers e
                 ON e.id = es.employer_id
WHERE               es.city_id = :id
");
        $stmt->bindValue('id', $city->getId());

        $stmt->execute();

        $first = true;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($first) {
                $first = false;
                $data .= UtilCsv::putCsv(array_keys($row))."\n";
            }
            $data .= UtilCsv::putCsv($row)."\n";
        }

        return DownloadFile::downloadGenerated($data, 'employer_survey-'.$city->getSlug().'.csv');
    }
     */
    public static function putCsv($input, $delimiter = ',', $enclosure = '"')
    {
        if (static::$isbuildin === null) {
            static::$isbuildin = function_exists('str_putcsv');
        }

        if (static::$isbuildin) {
            return str_putcsv($input, $delimiter, $enclosure);
        }

        return static::_str_putcsv($input, $delimiter, $enclosure);
    }
    protected static function _str_putcsv($input, $delimiter = ',', $enclosure = '"')
    {
        // Open a memory "file" for read/write...
        $fp = fopen('php://temp', 'r+');
        // ... write the $input array to the "file" using fputcsv()...
        fputcsv($fp, $input, $delimiter, $enclosure);
        // ... rewind the "file" so we can read what we just wrote...
        rewind($fp);
        // ... read the entire line into a variable...
        $data = fread($fp, 1048576);
        // ... close the "file"...
        fclose($fp);
        // ... and return the $data to the caller, with the trailing newline from fgets() removed.
        return rtrim($data, "\n");
    }
}
