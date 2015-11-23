<?php

namespace Stopsopa\UtilsBundle\Services\Elastic2;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractElastic2ProviderService {
    /**
     * @var Connection
     */
    protected $dbal;
    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * @return QueryBuilder
     */
    abstract public function getQueryBuilder();





    public function listData() {

    }
    public function updateRow($id, $data) {

    }
    public function insertRow($id, $data) {

    }
    public function deleteRow($id) {

    }
}