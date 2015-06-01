<?php

namespace Stopsopa\UtilsBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Stopsopa\UtilsBundle\Form\Validator\Constraints\MinMaxEntities;

/**
 */
class MinMaxEntitiesValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof MinMaxEntities) {
            throw new UnexpectedTypeException($constraint, get_class(new MinMaxEntities()));
        }

        $c = count($value);

        if ($c < $constraint->min || $c > $constraint->max) {
            // If you're using the new 2.5 validation API (you probably are!)
            $this->context->buildViolation($constraint->message)
                ->setParameter('%min%', $constraint->min)
                ->setParameter('%max%', $constraint->max)
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
