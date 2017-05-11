<?php

namespace Stopsopa\UtilsBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DisposableEmail extends Constraint
{
    const EMAIL_IS_DISPOSABLE = 1;

    public $message = 'This value is not a valid email address.';

    public function __construct($options = null)
    {
        parent::__construct($options);
    }
}
