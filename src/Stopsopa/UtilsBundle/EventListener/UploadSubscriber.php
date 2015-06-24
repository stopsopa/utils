<?php

namespace Stopsopa\UtilsBundle\EventListener;

use Exception;
use Stopsopa\UtilsBundle\Lib\AbstractException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
/**
 *
X-preSetData: 0
X-postSetData: 1
X-preBind: 2
X-bind: 3
X-postBind: 4
 * http://symfony.com/doc/current/cookbook/form/dynamic_form_modification.html#adding-an-event-subscriber-to-a-form-class
 * g(Adding an Event Subscriber to a Form Class)
 */
class UploadSubscriber implements EventSubscriberInterface
{
    protected $workintmpdir;
    protected $callback;
    protected $name;
    protected static $lastentity;
    public function __construct($workintmpdir, $callback, $name) {
        $this->workintmpdir = $workintmpdir;
        $this->callback = $callback;
        $this->name = $name;
    }

    public static function getSubscribedEvents() {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return array(
            FormEvents::PRE_SET_DATA    => 'preSetData',
//            FormEvents::POST_SET_DATA   => 'postSetData',
//            FormEvents::PRE_BIND        => 'preBind',
//            FormEvents::SUBMIT          => 'submit', // nie zawsze się odpala
            FormEvents::BIND            => 'bind',
//            FormEvents::POST_BIND       => 'postBind'
        );
    }
    public function execute($isfileuploaded, $builder = null) {
        $method = $this->callback;
        if ($method) {
            $method($isfileuploaded, $builder);
        }
        return $this;
    }

    public function preSetData(FormEvent $event) {
//        header('X-preSetData: '.Test::get());
        $this->_run($event, 'pre');
    }

    public function bind(FormEvent $event) {
//        header('X-bind: '.Test::get());
        $this->_run($event, 'bind');
    }
    protected function _run(FormEvent $event, $type) {

        $entity = $event->getData();
        $form = $event->getForm();

        AbstractException::setErrorHandler();

        if ($entity) {
            try {
                if ($entity->getFile()) {
//                    nieginie('jest');
//                    nieginie(get_class($entity).':'.$entity->getId());
//                    nieginie($entity, null, 1);
                    static::$lastentity = $entity;
                    $entity->tempdir = $this->workintmpdir;
                }

                $this->execute($entity ? $entity->getFile() : false, $form);

            } catch (Exception $ex) {
                throw $ex;
            }
        }

        AbstractException::delErrorHandler();
    }

    public static function getLastEntity() {
        return static::$lastentity;
    }












//    public function postSetData(FormEvent $event) {
////        header('X-postSetData: '.Test::get());
//        $entity = $event->getData();
//        $form = $event->getForm();
//        /* @var $entity \Stopsopa\UtilsBundle\Entity\Comment */
//        if ($entity instanceof \Stopsopa\UtilsBundle\Entity\Comment) {
//            nieginie($entity);
//        }
////        niechginie($entity);
////        $entity->tempdir = $this->workintmpdir;
//    }
//    public function preBind(FormEvent $event) {
////        header('X-preBind: '.Test::get());
//        $entity = $event->getData();
//        $form = $event->getForm();
//        /* @var $entity \Stopsopa\UtilsBundle\Entity\Comment */
//        if ($entity instanceof \Stopsopa\UtilsBundle\Entity\Comment) {
//            nieginie($entity);
//        }
////        niechginie($entity);
//        $entity->tempdir = $this->workintmpdir;
//    }
//    public function submit(FormEvent $event) { // nie zawsze się odpala
////        header('X-submit: '.Test::get());
//        $entity = $event->getData();
//        $form = $event->getForm();
//        /* @var $entity \Stopsopa\UtilsBundle\Entity\Comment */
//        if ($entity instanceof \Stopsopa\UtilsBundle\Entity\Comment) {
//            nieginie($entity);
//        }
////        niechginie($entity);
//        $entity->tempdir = $this->workintmpdir;
//    }
//    public function postBind(FormEvent $event) {
////        header('X-postBind: '.Test::get());
//        $entity = $event->getData();
//        $form = $event->getForm();
//
//        /* @var $entity \Stopsopa\UtilsBundle\Entity\Comment */
//        if ($entity instanceof \Stopsopa\UtilsBundle\Entity\Comment) {
//            nieginie($entity);
//        }
////        niechginie($entity);
//        $entity->tempdir = $this->workintmpdir;
//    }
//
////    public static function isFileInRequest($validate = false, FormBuilderInterface $context, $field) {
////        if ($validate) {
////            $request = AbstractApp::getRequest();
////            $files = $request->files->all();
////
////
//////    [3] => get
//////    [4] => remove
//////    [5] => has
//////    [6] => all
//////    [7] => count
//////    [8] => getFormConfig
//////    [9] => getForm
////            niechginie($context->getForm(), 2);
////            niechginie(get_class_methods($context), 2);
////
////        }
////        return false;
////    }
}