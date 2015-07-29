<?php

namespace Stopsopa\UtilsBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query;

abstract class AbstractManager {

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Connection
     */
    protected $dbal;
    /*
     * Nazwa tabeli w bazie
     */
    protected $table;

    /**
     * @var EntityRepository
     */
    protected $repository;
    protected $class;

    public function __construct(EntityManager $em, $class = null) {
        $this->em               = $em;
        $this->dbal             = $em->getConnection();
        if ($class) {
            $this->class        = $class;
            $this->repository   = $em->getRepository($this->class);
            $this->table        = $this->getTableName();
        }
    }
    public function count() {
        $table = $this->table;
        $stmt  = $this->dbal->query("
SELECT count(*) c FROM $table
");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
          return (int)$row['c'];

        return 0;
    }
    public function getTableName($class = null) {

        if (!$class) {

            if (!$this->table) {
                $this->table = $this->getClassMetadata()->getTableName();
            }

            return $this->table;
        }

        return $this->getClassMetadata($class)->getTableName();
    }
    public function getClass() {
        return $this->class;
    }
    public function getClassMetadata($class = null) {
        return $this->em->getClassMetadata($class ?: $this->class);
    }
    public function supportsEntity($entity) {
        return $entity instanceof $this->class;
    }

    public function remove($object, $flush = true) {

        $this->em->remove($object);
        $flush and $this->em->flush();

        return $this;
    }

    public function findOrThrow($id) {
        $entity = $this->find($id);

        if (!$entity)
            throw new NotFoundHttpException("Entity '{$this->class}' not found by id: '$id'");

        return $entity;
    }

    public function update($entity, $flush = true) {
        $this->em->persist($entity);
        $flush and $this->em->flush();
    }

    public function persist($entity) {
        $this->em->persist($entity);
        return $this;
    }

    public function flush($entity = null) {
        $this->em->flush($entity);
        return $this;
    }

    public function clean($entityName = null) {
        $this->em->clear($entityName);
        return $this;
    }

    public function refresh($entity) {
        $this->em->refresh($entity);
    }

    public function createEntity() {
        return new $this->class;
    }

    protected $idfield;
    public function find($id, $alias = null, $select = array(), $hydrationMode = null) {

        if (!$alias) {
            $alias = 'x';
        }

        if (!$hydrationMode) {
            $hydrationMode = Query::HYDRATE_OBJECT;
        }

        if (!$this->idfield) {
            $meta = $this->getClassMetadata();
            $this->idfield = $meta->identifier[0];
        }

        $qb = $this->_prepareSelect($alias, $select);
        return $qb
            ->where(
                $qb->expr()->eq(
                    $alias.'.'.$this->idfield,
                    $qb->expr()->literal($id)
                )
            )
            ->getQuery()
            ->getSingleResult($hydrationMode)
        ;
    }
    protected function _prepareSelect($alias, $select = array()) {

        $qb = $this->createQueryBuilder($alias);

        if (is_string($select)) {
            $select = preg_split('#[^a-z0-9_\.]+#i', $select);
        }

        if (!$select) {
           $select = array();
        }

        if (count($select)) {
            call_user_func_array(array($qb, 'select'), $select);
        }

        return $qb;
    }

    /**
     * $eman->findAllOrderBy('e', 'e.name', 'asc', array('e.id', 'e.username', 'e.path'), Query::HYDRATE_ARRAY);
     * $eman->findAllOrderBy('e', 'e.name', 'asc', 'e.id|e.username|e.path', Query::HYDRATE_ARRAY);
     * $eman->findAllOrderBy('e', 'e.name', 'asc', null, Query::HYDRATE_ARRAY); -- zwróć wszystie pola
     */
    public function findAllOrderBy($alias, $sort, $order = null, $select = array(), $hydrationMode = null) {

        if (!$hydrationMode) {
            $hydrationMode = Query::HYDRATE_OBJECT;
        }

        return $this->_prepareSelect($alias, $select)
            ->orderBy($sort, $order)
            ->getQuery()
            ->getResult($hydrationMode)
        ;
    }
    /**
     * $eman->findAll('e', array('e.id', 'e.username', 'e.path'), Query::HYDRATE_ARRAY);
     * $eman->findAll('e', 'e.id|e.username|e.path', Query::HYDRATE_ARRAY);
     * $eman->findAll('e', null, Query::HYDRATE_ARRAY); -- zwróć wszystie pola
     */
    public function findAll($alias = null, $select = array(), $hydrationMode = null) {

        if (!$alias) {
            $alias = 'x';
        }

        if (!$hydrationMode) {
            $hydrationMode = Query::HYDRATE_OBJECT;
        }

        return $this->_prepareSelect($alias, $select)
            ->getQuery()
            ->getResult($hydrationMode)
        ;
    }
    public function findOneByOrCreate($data, $update = false) {

        $entity = $this->findOneBy($data);

        if (!$entity) {
            /* @var $entity EmployerForm */
            $entity = $this->createEntity();

            $entity->set($data);

            if ($update) {
                $this->update($entity);
            }
        }

        return $entity;
    }

    /**
     * @param type $ids
     * @param false|true|QueryBuilder $qb
     *      false(default)  - tworzy qb, zwraca listę encji
     *      string          - tworzy qb, zwraca qb, z podanego stringu tworzy alias
     *      QueryBuilder    - ustawia odpowiedni warunek i zwraca obiekt z powrotem
     * @return type
     * @throws Exception
     */
    public function findByIds($ids, $qb = false) {

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $alias = 'x';

        if (is_string($qb)) {
            $alias = $qb;
        }

        $fid = $this->getClassMetadata()->getIdentifier();

        if (isset($fid[0])) {

            if ($qb instanceof QueryBuilder) {
                $b = $qb;
            }
            else {
                $b = $this->createQueryBuilder($alias);
            }

            $b->andWhere($b->expr()->in($alias.".".$fid[0], $ids));

            if ($qb) {
                return $b;
            }

            return $b->getQuery()->getResult();
        }

        throw new Exception("Entity {$this->class} has no identifier field");
    }

    public function findOneBy($values) {
        return $this->repository->findOneBy($values);
    }

    /**
     * Dla obsługi zapytań doctrine typu ->findOneBySlug()
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $args) {

        $key = 'OrThrow';

        if (strpos($method, $key) === strlen($method) - strlen($key)) {
            $throw      = true;
            $method     = substr($method, 0, -strlen($key));
        }
        else {
            $throw      = false;
        }

        $obj = $this;
        set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line, array $err_context) use ($obj, $method) {
              throw new Exception("Method '$method' doesn't exist in object '".$this->class."'");
        });
        $data =  call_user_func_array(array($this->repository, $method), $args);
        restore_error_handler();

        if ($throw) {
            if (is_array($data) && !count($data)) {
                throw new NotFoundHttpException("Entities '{$this->class}' not found by method '$method' and criteria: ".json_encode ($args));
            }
            elseif (!$data) {
                throw new NotFoundHttpException("Entity '{$this->class}' not found by method '$method' and criteria: ".json_encode ($args));
            }
        }

        return $data;
    }

    /**
     * @param string $alias
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilder($alias) {
        return $this->repository->createQueryBuilder($alias);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOrCreate($id) {
        $entity = $this->find($id);

        if (!$entity)
            $entity = $this->createEntity();

        return $entity;
    }
    public function getTableNameByClass($class = null) {
        return $this->getClassMetadata($class)->getTableName();
    }
    /**
     * @return Connection
     */
    public function getDbal() {
        return $this->dbal;
    }
    /**
     * @return EntityManager
     */
    public function getEntityManager() {
        return $this->em;
    }
}