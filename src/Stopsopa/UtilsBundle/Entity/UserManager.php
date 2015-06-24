<?php

namespace Stopsopa\UtilsBundle\Entity;

use DateTime;
use Stopsopa\UtilsBundle\Entity\AbstractManager;

/**
 * Stopsopa\UtilsBundle\Entity\UserManager
 */
class UserManager extends AbstractManager {
    const SERVICE = 'test.user.manager';

    public function find($id) {

        /* @var $entity User */
        $entity = parent::find($id);

        $entity->setUpdatedAt(new DateTime());

        foreach ($entity->getComments() as &$c) {
            $c->setUpdatedAt(new DateTime());
        }

        return $entity;
    }
}