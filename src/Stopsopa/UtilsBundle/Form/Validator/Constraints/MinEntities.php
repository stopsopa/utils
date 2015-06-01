<?php

namespace Stopsopa\UtilsBundle\Form\Validator\Constraints;

use Exception;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MinEntities extends Constraint
{
    public $message = 'The string "%string%" contains an illegal character: it can only contain letters or numbers.';
    public $num;
    public function __construct($options = null) {
        parent::__construct($options);

        if (empty($options['num'])) {
            throw new Exception("Brak parametru 'num'");
        }
    }
}
