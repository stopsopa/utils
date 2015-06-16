<?php

namespace Stopsopa\UtilsBundle\Form;

use Stopsopa\UtilsBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType {
    protected $create = true;
    public function __construct($create = true) {
        $this->create = $create;
    }
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $notblank       = new Assert\NotBlank();
        $builder
            ->add('name', null, array(
                'constraints'           => array(
                    $notblank
                ),
            ))
            ->add('surname', null, array(
                'constraints'           => array(
                    $notblank
                ),
            ))
            ->add('file', null, $this->create ? array(
                'constraints' => array(
                    $notblank
                ),
//                'file_path' => 'webPath',
//                'file_name' => 'name'
            ) : array())
            ->add('comments', 'collection', [
                'type'                  => new CommentType(false, false),
                'allow_add'             => true,
                'allow_delete'          => true,
                'by_reference'          => false,
//                'cascade_validation'    => true,
            ])
            ->add('submit', 'submit')
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        niechginie(User::getClassNamespace()); // nie wchodzi tutaj, nie ta wersja symfony
        $resolver->setDefaults(array(
            'data_class'            => User::getClassNamespace(),
//            'cascade_validation'    => true
        ));
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
//        niechginie(User::getClassNamespace());
        $resolver->setDefaults(array(
            'data_class'            => User::getClassNamespace(),
//            'cascade_validation'    => true
        ));
    }

    public function getName() {
        return 'user';
    }

}