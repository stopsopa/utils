<?php

namespace Stopsopa\UtilsBundle\Form;

use Stopsopa\UtilsBundle\Entity\Comment;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentType extends AbstractType {
    protected $submit = true;
    protected $create = true;
    public function __construct($submit = true, $create = true) {
        $this->submit = $submit;
        $this->create = $create;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $notblank       = new Assert\NotBlank();
        $builder
            ->add('comment', null, array(
                'constraints' => array(
                    $notblank
                ),
            ))
            ->add('createdAt', 'date', array(
                'widget' => 'single_text',
                'empty_value' => '',
                'trim' => true,
                'format' => 'yyyy-MM-dd', // http://symfony.com/doc/master/reference/forms/types/date.html#format
                'constraints' => array(
                    $notblank
                ),
                'attr' => array(
                    'placeholder' => 'YYYY-MM-DD'
                ),
            ))
            ->add('file', null, $this->create ? array(
                'constraints' => array(
                    $notblank
                ),
            ) : array())
        ;
        if ($this->submit) {
            $builder
                ->add('submit', 'submit')
            ;
        }
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        niechginie(Comment::getClassNamespace()); // nie wchodzi tutaj, nie ta wersja symfony
        $resolver->setDefaults(array(
            'data_class'            => Comment::getClassNamespace(),
//            'cascade_validation'    => true
        ));
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
//        niechginie(Comment::getClassNamespace());
        $resolver->setDefaults(array(
            'data_class'            => Comment::getClassNamespace(),
//            'cascade_validation'    => true
        ));
    }

    public function getName() {
        return 'comment';
    }

}