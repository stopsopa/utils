<?php

namespace Stopsopa\UtilsBundle\Services\Elastic2;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Stopsopa\UtilsBundle\Lib\Json\Json;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilArray;
use Exception;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilFilesystem;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilNested;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;
use Stopsopa\UtilsBundle\Lib\Json\Pretty\Json as PrettyJson;

class ElasticSearch2 {
    const SERVICE = 'elastic2';
    /**
     * @var Connection
     */
    protected $dbal;
    protected $config;
    protected $eshost;
    protected $esport;
    protected $eslog;
    protected $files;
    protected $url;

    public function __construct(Connection $connection, $config, $eshost, $esport, $eslog, $files)
    {
        $this->dbal         = $connection;
        $this->config       = $config;
        $this->eshost       = $eshost;
        $this->esport       = $esport;
        $this->eslog        = $eslog;
        $this->files        = $files;
        $this->eslogh       = $this->eslog;
        $this->eslogi       = 0;

        if (!file_exists($this->eslog)) {
            if (!mkdir($this->eslog, 0777, true)) {
                throw new Exception("Can't create direcoty '{$this->eslog}'");
            }
        }

        $this->eslog .= DIRECTORY_SEPARATOR.date('Y_m_d_H_i_s')."_populate.log";

        UtilFilesystem::checkIfFileExistOrICanCreate($this->eslog, true);

        $this->eslogh = fopen($this->eslog, 'a');

        $this->url          = $eshost.':'.$esport;

        if (!@is_array($this->config['indexes'])) {
            throw new Exception("Invalid config, no 'indexes' key");
        }

        foreach ($this->config['indexes'] as &$data) {
            foreach ($data['types'] as $type => &$tdata) {
                foreach ($tdata['properties'] as $field => &$properties) {
                    if (!isset($properties['mapping'])) {
                        $properties['mapping'] = array();
                    }
                    if (!isset($properties['mapping']['field'])) {
                        $properties['mapping']['field'] = $field;
                    }
                }
            }
        }
    }

    protected function _log($data) {
        $this->eslogi += 1;

        if (!is_string($data)) {
            $data = print_r($data, true);
        }

        fwrite($this->eslogh, "\n".date('Y-m-d H:i:s')."-------\n".$data);
    }
    /**
     * @param null|string $indexname (def: null) : null - wszystkie indexy, string - tylko konkretny index
     * @throws Exception
     */
    public function buildIndexes($indexname = null, OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $list = UtilArray::cascadeGet($this->config, 'indexes');

        if (is_array($list)) {

            foreach ($list as $index => &$data) {

                if (!$indexname || $indexname === $index) {

                    $output->writeln("Create index: '$index'");

                    // tworzenie indexu wraz z ustawieniami (analysis -> filter and analyzer
                    $settings = UtilArray::cascadeGet($data, 'settings', array());

                    $response = $this->api('PUT', "/$index?pretty", $settings);

                    if ( ! ( !empty($response['body']['acknowledged']) && $response['body']['acknowledged'] ) ) {
                        throw new Exception(print_r($response, true));
                    }

                    // tworzenie mappingów w indexach
                    $types = UtilArray::cascadeGet($data, 'types', array());

                    foreach ($types as $type => $data) {

                        $output->writeln("    - crete mapping '".$type."' in this index");

                        if (isset($data['mapping'])) {
                            unset($data['mapping']);
                        }

                        foreach ($data['properties'] as &$d) {
                            if (isset($d['mapping'])) {
                                unset($d['mapping']);
                            }
                        }

                        $res = $this->api('PUT', "/$index/_mapping/$type?pretty", array(
                            $type => $data
                        ));

                        if ($res['status'] !== 200) {
                            throw new Exception(print_r($res, true));
                        }
                    }
                }
                else {
                    $output->writeln("Ignore index: $index");
                }
            }
        }
        else {
            throw new Exception('List is not an array');
        }
    }
    public function dropIndexes($indexname = null, OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $list = UtilArray::cascadeGet($this->config, 'indexes');

        if (is_array($list)) {

            foreach ($list as $index => &$data) {
                if (!$indexname || $indexname === $index) {
                    $output->writeln("Delete index: $index");
                    $this->api('DELETE', "/$index");
                }
                else {
                    $output->writeln("Ignore index: $index");
                }
            }
        }
        else {
            throw new Exception('List is not an array');
        }
    }
    public function update($indexname, $type, $id, OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $list = UtilArray::cascadeGet($this->config, 'indexes');

        if (is_array($list)) {
            foreach ($list as $index => &$data) {

                if (!$indexname || $indexname === $index) {
                    $output->writeln("Populate index: $index");

                    foreach ($data['types'] as $_type => &$tdata) {

                        if ($_type === $type) {

                            $service = $this->getService(UtilArray::cascadeGet($tdata, 'mapping.service'));
//                            $service = $this->container->get(UtilArray::cascadeGet($tdata, 'mapping.service'));

                            $this->_update($service, $index, $type, $tdata, $id, $output);
                        }
                    }
                }
                else {
                    $output->writeln("Ignore index: $index");
                }
            }
        }
        else {
            throw new Exception('List is not an array');
        }
    }
    public function getService($name) {
//        $service = $this->container->get(UtilArray::cascadeGet($tdata, 'mapping.service'));
        return AbstractApp::get($name);
    }
    public function index($indexname, $type, $id, OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $list = UtilArray::cascadeGet($this->config, 'indexes');

        if (is_array($list)) {
            foreach ($list as $index => &$data) {

                if (!$indexname || $indexname === $index) {
                    $output->writeln("Populate index: $index");

                    foreach ($data['types'] as $_type => &$tdata) {

                        if ($_type === $type) {

//                            $service = $this->container->get();
                            $service = $this->getService(UtilArray::cascadeGet($tdata, 'mapping.service'));

                            $this->_index($service, $index, $type, $tdata, $id, $output);
                        }

                    }
                }
                else {
                    $output->writeln("Ignore index: $index");
                }
            }
        }
        else {
            throw new Exception('List is not an array');
        }
    }

    /**
     * https://www.elastic.co/guide/en/elasticsearch/guide/current/bulk.html
     */
    public function populate($indexname = null, OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $output->writeln("Start time: ".date('Y-m-d H:i:s'));

        $list = UtilArray::cascadeGet($this->config, 'indexes');

        if (is_array($list)) {

            foreach ($list as $index => &$data) {

                if (!$indexname || $indexname === $index) {
                    $output->writeln("Populate index: $index");

                    foreach ($data['types'] as $type => &$tdata) {

//                        $service = $this->container->get(UtilArray::cascadeGet($tdata, 'mapping.service'));
                            $service = $this->getService(UtilArray::cascadeGet($tdata, 'mapping.service'));

                        $this->_populate($service, $index, $type, $tdata, $output);
                    }
                }
                else {
                    $output->writeln("Ignore index: $index");
                }
            }
        }
        else {
            throw new Exception('List is not an array');
        }

        $output->writeln("End time: ".date('Y-m-d H:i:s'));

        fclose($this->eslogh);

        if ($this->eslogi) {
            $output->writeln("\n\n Check errorfile: {$this->eslog}");
        }
        else {
            if (file_exists($this->eslog)) {
                unlink($this->eslog);
            }
        }

    }
    public function getConfig() {
        return $this->config;
    }
    public function listIndexes(OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $data = $this->api('GET', "/*/_stats/store");

        return array_keys($data['body']['indices']);
    }
    protected function _populate($service, $index, $type, $tdata, OutputInterface $output = null) {

        /* @var $service AbstractDbalProvider */

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $output->writeln("    Populate type: '$type'");

        $atonce                 = UtilArray::cascadeGet($tdata, 'mapping.maxresults');

        $setupquerybuilder      = UtilArray::cascadeGet($tdata, 'mapping.setupquerybuilder');

        $useidfrom              = UtilArray::cascadeGet($tdata, 'mapping.useidfrom');

        $transform              = UtilArray::cascadeGet($tdata, 'mapping.transformermethod', null);

        call_user_func(array($service, 'setMaxResults'), $atonce);

        call_user_func(array($service, $setupquerybuilder), $atonce);

        $count = call_user_func(array($service, 'count'));

        $i = 0;

        $stack = array();

        foreach ($service as $offset => $group) {
            // różnica między index a create w bulk https://www.elastic.co/guide/en/elasticsearch/guide/current/bulk.html
            // index -> create or replace
            // create -> create, if exist fail

            $bulk = '';
            foreach ($group as &$r) {
                $row = array();

                if ($transform) {
                    $r = call_user_func(array($service, $transform), $r);
                }

                try {
                    foreach ($tdata['properties'] as $name => &$f) {
                        try {
                            $row[$name] = UtilNested::get($r, $f['mapping']['field']);
                        }
                        catch (Exception $e) {

                            if (is_array($r)) {
                                $dump = print_r($r, true);
                            }
                            else {
                                $dump = get_class($r);
                            }

                            throw new Exception("Exception message: '{$e->getMessage()}' data: '$dump'");
                        }
                    }

                    $tmp = array(
                        'index' => array(
                            "_index"    => $index,
                            "_type"     => $type,
                            "_id"       => $r[$useidfrom]
                        )
                    );
                }
                catch (Exception $e) {

                    if (is_array($r)) {
                        $dump = print_r($r, true);
                    }
                    else {
                        $dump = get_class($r);
                    }

                    throw new Exception("Exception message: '{$e->getMessage()}' data: '$dump'");
                }

                $tmp = json_encode($tmp, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."\n".json_encode($row, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."\n";

                $stack[$i] = $tmp;

                $bulk .= $tmp;

                $i += 1;
            }

//            $bulk .= "\n";

            $output->write("    Populate: $offset from $count, errors: {$this->eslogi}\r");

            $ret = $this->api('POST', "/_bulk", $bulk);

            foreach ($ret['body']['items'] as $_i => $ii) {
                if (!in_array($ii['index']['status'], array(200, 201))) {
                    $data   = $stack[$_i];
                    $status = $ii['index']['status'];
                    $error  = json_encode($ii);
                    $this->_log("
+++ Status:: $status
+++ Indexing error on data::
    $data
+++ Error::
    $error

");
                }
            }
        }

        $output->writeln("    Last row: $i ");
    }
    public function delete($indexname, $type, $id, OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $response = $this->api('DELETE', "/$indexname/$type/$id");

        $output->writeln(PrettyJson::encode($response));

    }
    protected function _update($service, $index, $type, $tdata, $id, OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $output->writeln("    Update element '$id' of type: '$type'");

//        $atonce                 = UtilArray::cascadeGet($tdata, 'mapping.maxresults');

//        $useidfrom              = UtilArray::cascadeGet($tdata, 'mapping.useidfrom');

        $setupquerybuilder      = UtilArray::cascadeGet($tdata, 'mapping.setupquerybuilder');

        $findbyid               = UtilArray::cascadeGet($tdata, 'mapping.findbyid');

        $transform              = UtilArray::cascadeGet($tdata, 'mapping.transformermethod');

        /* @var $qb QueryBuilder */
        $qb = call_user_func(array($service, $findbyid), $id);

        $r = $this->dbal->fetchAssoc($qb->getSQL());

        if ($transform) {
            $r = call_user_func(array($service, $transform), $r);
        }

        $row = array();

        foreach ($tdata['properties'] as $name => &$f) {
            $row[$name] = UtilNested::get($r, $f['mapping']['field']);
        }

        $output->write("    Update: $id\r");

        $result = $this->api('POST', "/$index/$type/$id/_update", array(
            'doc' => $row
        ));

        $output->writeln(PrettyJson::encode($result));
    }
    protected function _index($service, $index, $type, $tdata, $id, OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $output->writeln("    Update element '$id' of type: '$type'");

//        $atonce                 = UtilArray::cascadeGet($tdata, 'mapping.maxresults');

//        $useidfrom              = UtilArray::cascadeGet($tdata, 'mapping.useidfrom');

        $setupquerybuilder      = UtilArray::cascadeGet($tdata, 'mapping.setupquerybuilder');

        $findbyid               = UtilArray::cascadeGet($tdata, 'mapping.findbyid');

        $transform              = UtilArray::cascadeGet($tdata, 'mapping.transformermethod');

        /* @var $qb QueryBuilder */
        $qb = call_user_func(array($service, $findbyid), $id);

        $r = $this->dbal->fetchAssoc($qb->getSQL());

        if ($transform) {
            $r = call_user_func(array($service, $transform), $r);
        }

        $row = array();

        foreach ($tdata['properties'] as $name => &$f) {
            $row[$name] = UtilNested::get($r, $f['mapping']['field']);
        }

        $output->write("    Update: $id\r");

        $result = $this->api('PUT', "/$index/$type/$id", $row);

        $output->writeln(PrettyJson::encode($result));
    }
//    public function index($index, $type, $row, $setup = null) {
//        if (!$setup) {
//            $setup = &$this->config['indexes'][$index]['types'][$type];
//            niechginie($setup);
//        }
//
//    }
    /**
     * @param null $method
     * @param string $path
     * @param array $data
     * @param array $headers
     * @return array|string
     * @throws Exception
     * http://httpd.pl/bundles/toolssitecommon/tools/transform.php
     */
    public function api($method = null, $path = '', $data = array(), $headers = array())
    {
        if (!$method) {
            $method = 'GET';
        }

        $method = strtoupper($method);

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 10); 

        curl_setopt($ch, CURLOPT_ENCODING, '');

//        curl_setopt($ch, CURLOPT_USERPWD, $this->user.':'.$this->password);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        curl_setopt($ch, CURLOPT_URL, $this->url.$path);

        if (!is_string($data) && $data) {
            $data = Json::encode($data);
        }

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

//        $headers = array_merge($headers, array(
//            'Content-Type: application/json',
//        ));

        if (count($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_VERBOSE, true); // dobre do debugowania
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $response = null;
        $response = curl_exec($ch);

        // Then, after your curl_exec call:
        $header_size    = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header         = substr($response, 0, $header_size);
        $body           = substr($response, $header_size);

        $data = array();

        $data['body']   = Json::decode($body) ?: $body;
        $data['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($data['status'] === 0) {
            throw new Exception("Unable to connect to elasticsearch {$this->url}");
        }

        curl_close($ch);

        $header = explode("\n", $header);

        $hlist = array();
        foreach ($header as &$d) {
            $dd = explode(':', $d, 2);
            if (count($dd) === 2) {
                $hlist[$dd[0]] = trim($dd[1]);
            }
        }

        $data['header'] = $hlist;

        return $data;
    }
    public function __destruct()
    {
        if (!$this->eslogi) {
            if (file_exists($this->eslog)) {
                @unlink($this->eslog);
            }
        }
    }
}
