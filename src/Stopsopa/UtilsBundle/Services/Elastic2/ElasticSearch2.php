<?php

namespace Stopsopa\UtilsBundle\Services\Elastic2;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Stopsopa\UtilsBundle\Lib\Json\Json;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilArray;
use Exception;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilNested;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use PDO;
use Stopsopa\UtilsBundle\Lib\Json\Pretty\Json as PrettyJson;


class ElasticSearch2 {
    /**
     * @var Connection
     */
    protected $dbal;
    /**
     * @var Container
     */
    protected $container;
    protected $config;
    protected $eshost;
    protected $esport;
    protected $url;

    public function __construct(Container $container, Connection $connection, $config, $eshost, $esport)
    {
        $this->container    = $container;
        $this->dbal         = $connection;
        $this->config       = $config;
        $this->eshost       = $eshost;
        $this->esport       = $esport;
        $this->url          = $eshost.':'.$esport;

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

                    $response = $this->_api('PUT', "/$index?pretty", $settings);

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

                        $res = $this->_api('PUT', "/$index/_mapping/$type?pretty", array(
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
                    $this->_api('DELETE', "/$index");
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

                            $service = $this->container->get(UtilArray::cascadeGet($tdata, 'mapping.service'));

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

    /**
     * https://www.elastic.co/guide/en/elasticsearch/guide/current/bulk.html
     */
    public function populate($indexname = null, OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }


        $list = UtilArray::cascadeGet($this->config, 'indexes');

        if (is_array($list)) {

            foreach ($list as $index => &$data) {

                if (!$indexname || $indexname === $index) {
                    $output->writeln("Populate index: $index");

                    foreach ($data['types'] as $type => &$tdata) {

                        $service = $this->container->get(UtilArray::cascadeGet($tdata, 'mapping.service'));

                        $this->_fixtures($service, $index, $type, $tdata, $output);
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
    public function listIndexes(OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $data = $this->_api('GET', "/*/_stats/store");

        $list = array_keys($data['body']['indices']);

        foreach($list as $name) {
            $output->writeln("index: $name");
        }

    }
    protected function _fixtures($service, $index, $type, $tdata, OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $output->writeln("    Populate type: '$type'");

        $atonce                 = UtilArray::cascadeGet($tdata, 'mapping.maxresults');

        $setupquerybuilder      = UtilArray::cascadeGet($tdata, 'mapping.setupquerybuilder');

        $useidfrom              = UtilArray::cascadeGet($tdata, 'mapping.useidfrom');

        call_user_func(array($service, 'setMaxResults'), $atonce);

        call_user_func(array($service, $setupquerybuilder), $atonce);

        call_user_func(array($service, 'count'));

        $i = 0;
        foreach ($service as $offset => $group) {
            // różnica między index a create w bulk https://www.elastic.co/guide/en/elasticsearch/guide/current/bulk.html
            // index -> create or replace
            // create -> create, if exist fail

            $bulk = '';
            foreach ($group as &$r) {

                $row = array();

                foreach ($tdata['properties'] as $name => &$f) {
//                    try {
                        $row[$name] = UtilNested::get($r, $f['mapping']['field']);
//                    }
//                    catch (Exception $e) {
//                        niechginie(array(
//                            'row' => $r,
//                            'messa' => $e->getMessage()
//                        ), 2);
//                    }
                }

                $tmp = array(
                    'index' => array(
                        "_index"    => $index,
                        "_type"     => $type,
                        "_id"       => $row[$useidfrom]
                    )
                );

                $bulk .= json_encode($tmp)."\n".json_encode($row)."\n";

                $i += 1;
            }

            $output->write("    Populate: $offset\r");

            $this->_api('POST', "/_bulk", $bulk);
        }

        $output->writeln("    Last row: $i");
    }
    public function delete($indexname, $type, $id, OutputInterface $output = null) {

        if (!$output) {
            $output = new ConsoleOutput();
        }

        $response = $this->_api('DELETE', "/$indexname/$type/$id");

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

        /* @var $qb QueryBuilder */
        $qb = call_user_func(array($service, $findbyid), $id);

        $r = $this->dbal->fetchAssoc($qb->getSQL());

        $row = array();

        foreach ($tdata['properties'] as $name => &$f) {
            $row[$name] = UtilNested::get($r, $f['mapping']['field']);
        }

        $output->write("    Update: $id\r");

        $result = $this->_api('POST', "/$index/$type/$id/_update", array(
            'doc' => $row
        ));

        $output->writeln(PrettyJson::encode($result));
    }
//    public function index($index, $type, $row, $setup = null) {
//        if (!$setup) {
//            $setup = &$this->config['indexes'][$index]['types'][$type];
//            niechginie($setup);
//        }
//    }
    protected function _api($method = null, $path = '', $data = array(), $headers = array())
    {
        if (!$method) {
            $method = 'GET';
        }

        $method = strtoupper($method);

        $ch = curl_init();

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
}
