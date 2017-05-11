<?php

namespace Stopsopa\UtilsBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Stopsopa\UtilsBundle\Lib\DisposableEmails as StaticValidator;

/**
 */
class DisposableEmailValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DisposableEmail) {
            throw new UnexpectedTypeException($constraint, get_class(new DisposableEmail()));
        }

        $c = count($value);

        if (StaticValidator::isDisposable($value)) {
            $this->buildViolation($constraint->message)
                ->setInvalidValue($value)
                ->setCode(DisposableEmail::EMAIL_IS_DISPOSABLE)
                ->addViolation();

            return;
        }
    }
}
