<?php

namespace Stopsopa\UtilsBundle\Form;

use Stopsopa\UtilsBundle\Entity\TestUser;
use Stopsopa\UtilsBundle\Entity\TestComment;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TestUserType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $notblank       = new Assert\NotBlank();
        $builder
            ->add('name', null, array(
                'label' => 'Imię',
                'constraints' => array(
                    $notblank
                ),
            ))
            ->add('surname', null, array(
                'label' => 'Nazwisko',
                'constraints' => array(
                    $notblank
                ),
            ))
            ->add('submit', 'submit')
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
//        niechginie(Employer::getClassNamespace());
        $resolver->setDefaults(array(
            'data_class' => Employer::getClassNamespace(),
        ));
    }

    public function getName() {
        return 'test_user';
    }

}