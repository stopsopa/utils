<?php

namespace Stopsopa\UtilsBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
    'constraints'     => [
        new Assert\NotBlank(array(
            'message' => 'Wpisz e-mail'
        )),
        new Assert\Email(array(
            'message' => 'E-mail nie jest prawidłowy'
        )),
        new DisposableEmail(array(
            'message' => 'E-mail nie jest prawidłowy'
        )),
        (new UniqueInDb(array(
            'message' => 'E-mail "{{ unique }}" jest zajęty'
        )))->setDbOptions(array(
            'em'    => $this->container->get('doctrine.orm.entity_manager'),
            'table' => 'users',
            'field' => 'email'
        ))
    ]
 */
class UniqueInDb extends Constraint
{
    const NOT_UNIQUE = 1;

    public $message = 'Value "{{ unique }}" is not unique';
    protected $db;

    public function __construct($options = null)
    {
        parent::__construct($options);
    }
    public function setDbOptions($db = array()) {
        $this->db = $db;
        return $this;
    }
    public function getDbOptions() {
        return $this->db;
    }
}
