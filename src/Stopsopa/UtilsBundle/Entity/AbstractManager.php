<?php

namespace Stopsopa\UtilsBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;

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
    public function getTableName() {
        if (!$this->table) {
            $this->table = $this->getClassMetadata()->getTableName();
        }

        return $this->table;
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

    public function find($id) {
        return $this->repository->find($id);
    }

    public function findAll() {
        return $this->repository->findAll();
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
      $obj = $this;
      set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line, array $err_context) use ($obj, $method) {
            throw new Exception("Method '$method' doesn't exist in object '".$this->class."'");
      });
      $data =  call_user_func_array(array($this->repository, $method), $args);
      restore_error_handler();
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
    public function findAllOrderBy($field, $order = 'asc') {
        return $this->createQueryBuilder('s')->orderBy('s.'.$field, $order)->getQuery()->getResult();
    }
    public function getTableNameByClass($class = null) {
        return $this->getClassMetadata($class)->getTableName();
    }
}