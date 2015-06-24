<?php

namespace Stopsopa\UtilsBundle\Entity;

use DateTime;
use Stopsopa\UtilsBundle\Entity\AbstractManager;

/**
 * Stopsopa\UtilsBundle\Entity\CommentManager
 */
class CommentManager extends AbstractManager {
    const SERVICE = 'test.comment.manager';

    public function find($id) {
        
        /* @var $entity Comment */
        $entity = parent::find($id);

        $entity->setUpdatedAt(new DateTime());

        return $entity;
    }
}