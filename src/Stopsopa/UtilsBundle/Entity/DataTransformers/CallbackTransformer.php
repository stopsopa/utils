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
 * Jak już to zrobiłem to widzę że jest coś takiego standardowo w symfony:
 * Symfony\Component\Form\CallbackTransformer
 *
 *
 *
            ->add( // lista encji jako checkboxy , patrz też jak zrobić transformer http://symfony.com/doc/master/cookbook/form/data_transformers.html
                $builder->create( // jeśli chodzi o listowanie encji jako checkboxy to należy robić tak jak powyżej, nie jak tutaj
                    'locations', // bo jak zrobimy tak to lista $list jest montowana ze wszystkich encji ale w oderwaniu od podzbioru już wybranych elementów w ramach encji
                    'collection', // w rezultacie przy kolejnych wejściach do formularza edycji nie będzie nigdy nic zaznaczone że jest już wybrane
                    array(
                        'label' => 'Adresy',
                        'allow_add'     => true,
                        'allow_delete'  => true,
                        'type' => new LocationType()
                    )
                )
                ->addModelTransformer(new CallbackTransformer(function ($list) {
                    return $list;
                }, function ($list) {
                    $tmp = array();

                    foreach ($list as &$id) {
                        if (is_array($id)) {
                            $tmp[] = $id;
                        }
                        else {
                            $tmp[] = App::getDbalEmployerLocations()->find($id);
                        }
                    }

                    return $tmp;
                }))
            )
 *
 *
 *
            ->add( // lista encji jako checkboxy , patrz też jak zrobić transformer http://symfony.com/doc/master/cookbook/form/data_transformers.html
                $builder->create( // jeśli chodzi o listowanie encji jako checkboxy to należy robić tak jak powyżej, nie jak tutaj
                    'industries', // bo jak zrobimy tak to lista $list jest montowana ze wszystkich encji ale w oderwaniu od podzbioru już wybranych elementów w ramach encji
                    'choice', // w rezultacie przy kolejnych wejściach do formularza edycji nie będzie nigdy nic zaznaczone że jest już wybrane
                    array(
                        'choices'     => $industries,
                        'multiple'    => true,
                    )
                )
                ->addModelTransformer(new CallbackTransformer(function ($list) {
                    $tmp = array();

                    foreach ($list as &$d) {
                        $tmp[] = $d['id'];
                    }

                    return $tmp;
                }, function ($list) {
                    $tmp = array();

                    foreach ($list as &$id) {
                        $tmp[] = App::getDbalIndustries()->find($id);
                    }

                    return $tmp;
                }))
            )
 *
 *
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
        return call_user_func($this->transform, $list);
    }
    public function reverseTransform($list) {
        return call_user_func($this->reverse, $list);
    }
}
