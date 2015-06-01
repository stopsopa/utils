<?php

namespace Stopsopa\UtilsBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 */
class MinEntitiesValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        niechginie($value);

        if (!preg_match('/^[a-zA-Za0-9]+$/', $value, $matches)) {
            // If you're using the new 2.5 validation API (you probably are!)
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();

            // If you're using the old 2.4 validation API
            /*
            $this->context->addViolation(
                $constraint->message,
                array('%string%' => $value)
            );
            */
        }
    }
}
