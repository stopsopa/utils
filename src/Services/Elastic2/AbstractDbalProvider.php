<?php

namespace Stopsopa\UtilsBundle\Services\Elastic2;
use Doctrine\DBAL\Query\QueryBuilder;
use Iterator;
use Doctrine\DBAL\Connection;

abstract class AbstractDbalProvider implements Iterator {


    protected $maxresults;
    protected $offset;
    protected $count;

    /**
     * @var QueryBuilder
     */
    protected $qb;
    /**
     * @var Connection
     */
    protected $dbal;
    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }
    public function setMaxResults($maxresults) {
        $this->maxresults = $maxresults;
    }
    public function count() {

        // tutaj zrobić clone bo nadpisujemy ->select()
        /* @var $qb QueryBuilder */
//        $qb = clone $this->qb;

        $qb = $this->dbal->createQueryBuilder();

        $qb
            ->select('count(*) c')
            ->from('(' . $this->qb->getSQL() . ')', 'xxx')
        ;

        $row = $this->dbal->fetchAssoc($qb->getSQL());

        return $this->count = intval($row['c']);
    }
    public function rewind() {

        if (!$this->maxresults) {
            throw new Exception('First setup $this->maxresults');
        }

        $this->offset = 0;

        $this->qb
            ->setFirstResult($this->offset)
            ->setMaxResults($this->maxresults)
        ;
    }
    public function current() {
        return $this->dbal->fetchAll($this->qb->getSQL());
    }
    public function key() {
        return $this->offset;
    }
    public function next() {

        $this->offset += $this->maxresults;

        $this->qb->setFirstResult($this->offset);
    }
    public function valid() {
        return $this->offset < $this->count;
    }
}