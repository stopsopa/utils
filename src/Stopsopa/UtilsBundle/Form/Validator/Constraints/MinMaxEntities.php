<?php

namespace Stopsopa\UtilsBundle\Form\Validator\Constraints;

use Exception;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MinMaxEntities extends Constraint
{
    public $message = 'Choose min %min% and max %max% entities from list';
    public $min;
    public $max;
    public function __construct($options = null) {
        parent::__construct($options);

        if (empty($options['min'])) {
            throw new Exception("Brak parametru 'min'");
        }

        if (empty($options['max'])) {
            throw new Exception("Brak parametru 'max'");
        }
    }
}
