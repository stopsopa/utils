<?php

namespace Stopsopa\UtilsBundle\Form\Validator\Constraints;

use Exception;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CountEntities extends Constraint
{
    const TOO_SHORT_ERROR = 1;
    const TOO_LONG_ERROR = 2;
    
    public $maxMessage      = 'Choose max {{ max }} entities from list';
    public $minMessage      = 'Choose min {{ min }} entities from list';
    public $message         = 'Choose min {{ min }} and max {{ max }} entities from list';
    public $exactMessage    = 'Choose exactly {{ min }} entities from list';
    public $min;
    public $max;

    public function __construct($options = null) {

        if (null !== $options && !is_array($options)) {
            $options = array(
                'min' => $options,
                'max' => $options,
            );
        }

        parent::__construct($options);

        if (null === $this->min && null === $this->max) {
            throw new MissingOptionsException(sprintf('Either option "min" or "max" must be given for constraint %s', __CLASS__), array('min', 'max'));
        }
    }
}
