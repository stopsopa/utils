<?php

namespace Stopsopa\UtilsBundle\Form\Validator\Constraints;

use PDO;
use Stopsopa\UtilsBundle\Lib\DisposableEmails as StaticValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 */
class UniqueInDbValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueInDb) {
            throw new UnexpectedTypeException($constraint, get_class(new UniqueInDb()));
        }

        /* @var $constraint UniqueInDb */

        $config = $constraint->getDbOptions();

//        'em'    => $this->container->get('doctrine.orm.entity_manager'),
//        'table' => 'users',
//        'field' => 'email'

        $query = "
SELECT          count(*) c
FROM            {$config['table']}
WHERE           {$config['field']} = :value
";
        $stmt = $config['em']->getConnection()->prepare($query);

        $stmt->bindValue('value', $value);

        $stmt->execute();

        $c = 1;
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $c = (int)$row['c'];
        }

        if ($c) {
            $this->buildViolation($constraint->message)
                ->setParameter('{{ unique }}', $value)
                ->setInvalidValue($value)
                ->setCode(UniqueInDb::NOT_UNIQUE)
                ->addViolation()
            ;
        }
    }
}
