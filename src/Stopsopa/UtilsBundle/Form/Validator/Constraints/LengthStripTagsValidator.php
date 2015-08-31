<?php

namespace Stopsopa\UtilsBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 */
class LengthStripTagsValidator extends ConstraintValidator
{
    public static function process($data) {
        // logika ta odpowiada po stronie serwera takiemu czemuś :
//        var c = ed.getContent();
//        c = $('<textarea />').html(c).text();
//        c = c.replace(/<[^>]*>/g, ' ').replace(/[\s\n\t\r]+/g, ' ');

        $data = html_entity_decode($data);
        $data = preg_replace('#<[^>]*>#', ' ', $data);
        $data = preg_replace('#[\s\r\n\xC2\xA0\t]+#i', ' ', $data);

        return $data;
    }

    public function validate($value, Constraint $constraint)
    {
        // wszystkie tagi zastępuje jedną spacją

        $value = static::process($value);


        if (!$constraint instanceof LengthStripTags) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\LengthStripTags');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $stringValue = (string) $value;
        $invalidCharset = false;

        if ('UTF8' === $charset = strtoupper($constraint->charset)) {
            $charset = 'UTF-8';
        }

        if (function_exists('iconv_strlen')) {
            $length = @iconv_strlen($stringValue, $constraint->charset);
//            nieginie($stringValue);
//            nieginie(mb_strlen($stringValue, 'utf-8'));
//            niechginie($length);
            $invalidCharset = false === $length;
        } elseif (function_exists('mb_strlen')) {
            if (mb_check_encoding($stringValue, $constraint->charset)) {
                $length = mb_strlen($stringValue, $constraint->charset);
            } else {
                $invalidCharset = true;
            }
        } elseif ('UTF-8' !== $charset) {
            $length = strlen($stringValue);
        } elseif (!preg_match('//u', $stringValue)) {
            $invalidCharset = true;
        } elseif (function_exists('utf8_decode')) {
            $length = strlen(utf8_decode($stringValue));
        } else {
            preg_replace('/./u', '', $stringValue, -1, $length);
        }

        if ($invalidCharset) {
            $this->buildViolation($constraint->charsetMessage)
                ->setParameter('{{ value }}', $this->formatValue($stringValue))
                ->setParameter('{{ charset }}', $constraint->charset)
                ->setInvalidValue($value)
                ->addViolation();

            return;
        }

        if (null !== $constraint->max && $length > $constraint->max) {
            $this->buildViolation($constraint->min == $constraint->max ? $constraint->exactMessage : $constraint->maxMessage)
                ->setParameter('{{ value }}', $this->formatValue($stringValue))
                ->setParameter('{{ limit }}', $constraint->max)
                ->setInvalidValue($value)
                ->setPlural((int) $constraint->max)
                ->setCode(LengthStripTags::TOO_LONG_ERROR)
                ->addViolation();

            return;
        }

        if (null !== $constraint->min && $length < $constraint->min) {
            $this->buildViolation($constraint->min == $constraint->max ? $constraint->exactMessage : $constraint->minMessage)
                ->setParameter('{{ value }}', $this->formatValue($stringValue))
                ->setParameter('{{ limit }}', $constraint->min)
                ->setInvalidValue($value)
                ->setPlural((int) $constraint->min)
                ->setCode(LengthStripTags::TOO_SHORT_ERROR)
                ->addViolation();
        }
    }
}
