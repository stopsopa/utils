<?php

namespace Stopsopa\UtilsBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Stopsopa\UtilsBundle\Lib\Request;
use Stopsopa\UtilsBundle\Entity\UserManager;
use Stopsopa\UtilsBundle\Entity\CommentManager;
use Stopsopa\UtilsBundle\Form\UserType;
use Symfony\Component\Form\FormView;
use Stopsopa\UtilsBundle\Entity\User;


use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormError;

use Stopsopa\UtilsBundle\EventListener\UploadSubscriber;

/**
 * @Route("/test/upload")

routing.yml

stopsopautils:
    resource: "@StopsopaUtilsBundle/Controller/"
    type:     annotation
 */
class TestUploadController extends AbstractController {
    /**
     * @Route("", name="test-upload")
     */
    public function upload(Request $request) {

        /* @var $man UserManager */
        $man = $this->get(UserManager::SERVICE);

        return $this->render('StopsopaUtilsBundle:upload:index.html.twig', array(
            'users' => $man->findAll()
        ));
    }
    /**
     * @Route("/create", name="test-upload-create")
     */
    public function createAction(Request $request) {

        /* @var $man UserManager */
        $man = $this->get(UserManager::SERVICE);

        /* @var $entity User */
        $entity = $man->createEntity();

        $type = new UserType();

        $action = $this->generateUrl('test-upload-create');

        $form = $this->createForm($type, $entity, array(
            'action' => $action,
        ));

        if ($request->isPost()) {

            $form->handleRequest($request);

            if ($form->get('submit')->isClicked()) {

                if ($form->isValid()) {

                    // ręcznie trzeba wywołać
                    $entity->preUpload(true);
                    foreach ($entity->getComments() as $c) {
                        $c->preUpload(true);
                    }

                    $man->update($entity);

                    $this->setNotification($request, 'Created');

                    return $this->redirect($action);
                }
//                else {
//                    niechginie($entity->getComments());
//                }
            }
            else { // tylko upload pliku
                if ($request->files->count()) { // jeśli w ogóle coś jest {

                    // tutaj się dzieją ważne rzeczy
                    $type = new UserType(true);
                    $form = $this->createForm($type, $entity, array(
                        'action'                => $action,
                        'validation_groups'     => array('upload')
                    ));

                    $form->handleRequest($request);

                    if ($form->isValid()) {

                        $dbal = $man->getDbal();
                        $dbal->beginTransaction(); // suspend auto-commit

//                        nieginie($entity->getComments());
                            $man->update($entity);

                            $uploadedEntity = UploadSubscriber::getLastEntity();

                            $response = $this->getJsonResponse(array(
                                'files' => array(
                                    array(
                                        'hidden'    => $uploadedEntity->getPath(),
                                        'webPath'   => $uploadedEntity->getWebPath()
                                    )
                                )
                            ));

                        $dbal->rollback();

                        return $response;
                    }
                    else {
                        niechginie($this->getErrors($form));
                        die('not valid');
                    }
                }
            }
        }

        return $this->render('StopsopaUtilsBundle:upload:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    /**
     * @Route("/edit/{id}", name="test-upload-edit", requirements={"id"="\d+"})
     */
    public function editAction(Request $request, $id) {

        /* @var $man UserManager */
        $man = $this->get(UserManager::SERVICE);

        /* @var $entity User */
        $entity = $man->findOrThrow($id);

        $type = new UserType();

        $editurl = $this->generateUrl($request, array(
            'id' => $entity->getId()
        ));

        $form = $this->createForm($type, $entity, array(
            'action' => $editurl,
        ));

        if ($request->isPost()) {

            $form->handleRequest($request);

            if ($form->get('submit')->isClicked()) {

                if ($form->isValid()) {

                    // ręcznie trzeba wywołać
                    $entity->preUpload(true);
                    foreach ($entity->getComments() as $c) {
                        $c->preUpload(true);
                    }

                    $man->update($entity);

                    $this->setNotification($request, 'Edited');

                    return $this->redirect($editurl);
                }
            }
            else { // tylko upload pliku
                if ($request->files->count()) { // jeśli w ogóle coś jest {

                    // tutaj się dzieją ważne rzeczy
                    $type = new UserType(true);
                    $form = $this->createForm($type, $entity, array(
                        'action'                => $editurl,
                        'validation_groups'     => array('upload')
                    ));

                    $form->handleRequest($request);

                    if ($form->isValid()) {

                        $man->update($entity);

                        return $this->getJsonResponse(array(
                            'files' => array(
                                array(
                                    'hidden'    => $uploadedEntity->getPath(),
                                    'webPath'   => $uploadedEntity->getWebPath()
                                )
                            )
                        ));
                    }
                    else {
                        niechginie($this->getErrors($form));
                        die('not valid');
                    }
                }
            }
        }

        return $this->render('StopsopaUtilsBundle:upload:edit.html.twig', array(
            'form'   => $form->createView(),
            'entity' => $entity
        ));
    }

    /**
     * @Route("/delete/{id}", name="test-upload-delete", requirements={"id"="\d+"})
     */
    public function deleteAction(Request $request, $id) {

        /* @var $man UserManager */
        $man = $this->get(UserManager::SERVICE);

        /* @var $entity User */
        $entity = $man->findOrThrow($id);

        $man->remove($entity);

        $this->setNotification($request, 'Deleted');

        return $this->redirect($request->headers->get('referer'));

    }
}