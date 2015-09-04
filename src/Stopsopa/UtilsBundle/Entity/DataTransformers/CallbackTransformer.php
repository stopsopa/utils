<?php

namespace Stopsopa\UtilsBundle\Entity\DataTransformers;

use Acme\TaskBundle\Entity\Issue;
use AppBundle\Entity\City;
use Doctrine\ORM\EntityManager;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilArray;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Stopsopa\UtilsBundle\Entity\DataTransformers\CallbackTransformer.
 */
class CallbackTransformer implements DataTransformerInterface
{
    protected $transform;
    protected $reverse;
    public function __construct($transform, $reverse) {
        $this->transform    = $transform;
        $this->reverse      = $reverse;
    }
    public function transform($list) {
        $transform = $this->transform;
        return $transform($list);
    }
    public function reverseTransform($list) {
        $reverse = $this->reverse;
        return $reverse($list);
    }
}
