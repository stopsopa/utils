<?php

namespace Stopsopa\UtilsBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 */
class CountEntitiesValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CountEntities) {
            throw new UnexpectedTypeException($constraint, get_class(new CountEntities()));
        }

        $c = count($value);

        if (null !== $constraint->max && $c > $constraint->max) {
            $this->buildViolation($constraint->min == $constraint->max ? $constraint->exactMessage : $constraint->maxMessage)
//                ->setParameter('{{ value }}', $c)
                ->setParameter('{{ max }}', $constraint->max)
                ->setInvalidValue($value)
                ->setPlural((int) $constraint->max)
                ->setCode(CountEntities::TOO_LONG_ERROR)
                ->addViolation();

            return;
        }

        if (null !== $constraint->min && $c < $constraint->min) {
            $this->buildViolation($constraint->min == $constraint->max ? $constraint->exactMessage : $constraint->minMessage)
//                ->setParameter('{{ value }}', $c)
                ->setParameter('{{ min }}', $constraint->min)
                ->setInvalidValue($value)
                ->setPlural((int) $constraint->min)
                ->setCode(CountEntities::TOO_SHORT_ERROR)
                ->addViolation();
        }
    }
}
