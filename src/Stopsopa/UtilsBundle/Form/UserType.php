<?php

namespace Stopsopa\UtilsBundle\Form;

use Stopsopa\UtilsBundle\Entity\User;
use Stopsopa\UtilsBundle\EventListener\UploadSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType {
    protected $validateuploads = true;
    public function __construct($validateuploads = true) {
        $this->validateuploads = $validateuploads;
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
            ->add('comments', 'collection', [
                'type'                  => new CommentType($this->validateuploads, false),
                'allow_add'             => true,
                'allow_delete'          => true,
                'by_reference'          => false,
//                'cascade_validation'    => true,
            ])
            ->add('submit', 'submit')
        ;

        $subscriber = new UploadSubscriber($this->validateuploads, function ($isfileuploaded, $builder) {
            $builder
                ->add('file', null, $isfileuploaded ? array(
                    'constraints' => array(
                        new Assert\NotBlank(array(
                            'groups' => array('upload')
                        ))
                    ),
    //                'file_path' => 'webPath',
    //                'file_name' => 'name'
                ) : array())
            ;
        });

        $builder->addEventSubscriber($subscriber);

//form.pre_set_data
//form.post_set_data
//form.pre_bind
//form.pre_bind
//submit
//form.bind
//form.post_bind
//form.post_bind

//X-form.pre_set_data: 0
//X-form.post_set_data: 1
//X-form.pre_bind: 3
//X-form.bind: 5
//X-form.post_bind: 7
//
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//            $entity     = $event->getData();
//            $form       = $event->getForm();
//            if (!$entity || null === $entity->getId()) {
//                $form->add('name', 'text');
//            }
//        });
//
//        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
//        });
//
//        $builder->addEventListener(FormEvents::PRE_BIND, function (FormEvent $event) {
//        });
//
//        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
//        });
//
//        $builder->addEventListener(FormEvents::BIND, function (FormEvent $event) {
//        });
//
//        $builder->addEventListener(FormEvents::POST_BIND, function (FormEvent $event) {
//        });
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