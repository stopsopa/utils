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
    protected $workintmpdir = true;
    public function __construct($workintmpdir = false, $submit = true) {
        $this->submit = $submit;
        $this->workintmpdir = $workintmpdir;
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

        $builder->add('path', 'hidden');
        
        $subscriber = new UploadSubscriber($this->workintmpdir, function ($isfileuploaded, $builder) {
            $builder
                ->add('file', null, $isfileuploaded ? array(
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'groups' => array('upload')
                        ))
                    ),
                ) : array())
            ;
        }, 'comment');

        $builder->addEventSubscriber($subscriber->execute(false, $builder));
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