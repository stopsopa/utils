<?php

namespace Stopsopa\UtilsBundle\Services\Elastic2\Example;

use Stopsopa\UtilsBundle\Services\Elastic2\AbstractElastic2ProviderService;
use Doctrine\DBAL\Query\QueryBuilder;
use Stopsopa\UtilsBundle\Services\Elastic2\AbstractDbalProvider;

/**
 * AppBundle\Services\Elastic2\TestProvider
 */
class TestIndexnameProvider extends AbstractDbalProvider {
    const SERVICE = 'elastic.testindexname.provider';
    /**
     * @return QueryBuilder
     */
    public function test_Qb() {

        $qb = $this->dbal->createQueryBuilder();

        $this->qb = $qb
            ->from('es_users', 'u')
            ->select(array(
                'u.id',
                'u.surname name'
            ))
            ->where($qb->expr()->like("u.surname", $qb->expr()->literal("%a%")))
            ->andWhere($qb->expr()->notLike("u.surname", $qb->expr()->literal("%b%")))
        ;
    }

    /**
     * @param $id
     * @return QueryBuilder
     */
    public function test_FindById($id) {

        $qb = $this->dbal->createQueryBuilder();
        /* @var $qb QueryBuilder */

        return $qb
            ->from('es_users', 'u')
            ->select(array(
                'u.id',
                'u.surname name'
            ))
            ->where($qb->expr()->eq('u.id', $qb->expr()->literal($id)))
        ;
    }
    /**
     * @return QueryBuilder
     */
    public function test2_Qb() {

        $qb = $this->dbal->createQueryBuilder();

        $this->qb = $qb
            ->from('es_users', 'u')
            ->select(array(
                'u.id',
                'u.surname name'
            ))
            ->where($qb->expr()->like("u.surname", $qb->expr()->literal("%b%")))
            ->andWhere($qb->expr()->notLike("u.surname", $qb->expr()->literal("%a%")))
        ;
    }
    /**
     * @param $id
     * @return QueryBuilder
     */
    public function test2_FindById($id) {

        $qb = $this->dbal->createQueryBuilder();
        /* @var $qb QueryBuilder */

        return $qb
            ->from('es_users', 'u')
            ->select(array(
                'u.id',
                'u.surname name'
            ))
            ->where($qb->expr()->eq('u.id', $qb->expr()->literal($id)))
        ;
    }

}