<?php

namespace Stopsopa\UtilsBundle\Form;

use Stopsopa\UtilsBundle\Entity\Comment;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Stopsopa\UtilsBundle\EventListener\UploadSubscriber;

class CommentType extends AbstractType {
    protected $submit = true;
    protected $validateuploads = true;
    public function __construct($validateuploads = true, $submit = true) {
        $this->submit = $submit;
        $this->validateuploads = $validateuploads;
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
                'widget'        => 'single_text',
                'empty_value'   => '',
                'trim'          => true,
                'format'        => 'yyyy-MM-dd', // http://symfony.com/doc/master/reference/forms/types/date.html#format
                'constraints' => array(
                    $notblank
                ),
                'attr' => array(
                    'placeholder' => 'YYYY-MM-DD'
                ),
            ))
//            ->add('file', null, UploadSubscriber::isFileInRequest($this->validateuploads, $builder, 'file') ? array(
        ;
        if ($this->submit) {
            $builder
                ->add('submit', 'submit')
            ;
        }

        $subscriber = new UploadSubscriber($this->validateuploads, function ($isfileuploaded, $builder) {
            $builder
                ->add('file', null, $isfileuploaded ? array(
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'groups' => array('upload')
                        ))
                    ),
                ) : array())
                ->add('path', 'hidden')
            ;
        });

        $builder->add('path', 'hidden');

        $builder->addEventSubscriber($subscriber);
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