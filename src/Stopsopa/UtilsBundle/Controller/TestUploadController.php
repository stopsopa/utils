<?php

namespace Stopsopa\UtilsBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Stopsopa\UtilsBundle\Lib\Request;
use Stopsopa\UtilsBundle\Entity\UserManager;
use Stopsopa\UtilsBundle\Entity\CommentManager;
use Stopsopa\UtilsBundle\Form\UserType;
use Symfony\Component\Form\FormView;
use Stopsopa\UtilsBundle\Entity\User;

/**
 * @Route("/test/upload")
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

        $form = $this->createForm($type, $entity, array(
            'action' => $this->generateUrl($request->get('_route')),
            'attr' => array(
                'novalidate' => ''
            )
        ));

        if ($request->isPost()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $man->update($entity);

                $this->setNotification($request, 'Created');

                return $this->redirect($this->generateUrl('test-upload'));
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

        $type = new UserType(false);

        $editurl = $this->generateUrl($request->get('_route'), array(
            'id' => $entity->getId()
        ));

        $form = $this->createForm($type, $entity, array(
            'action' => $editurl
        ));

        if ($request->isPost()) {

//            nieginie($_SERVER, 2);
//            nieginie($_POST, 2);
//            nieginie($_GET, 2);
//            nieginie($_FILES, 2);
//            return $this->getJsonResponse($_POST);
//            return $this->getJsonResponse($request->request->get('_blueimp'));

            $form->handleRequest($request);

            if ($form->get('submit')->isClicked()) {

                if ($form->isValid()) {

                    // ręcznie trzeba wywołać
                    // ręcznie trzeba wywołać
                    // ręcznie trzeba wywołać
                    $entity->preUpload();
                    foreach ($entity->getComments() as $c) {
                        $c->preUpload();
                    }

                    $man->update($entity);

                    $this->setNotification($request, 'Edited');

                    return $this->redirect($editurl);
                }
            }
            else { // tylko upload pliku
                if ($request->files->all()) { // jeśli w ogóle coś jest {



                    return $this->getJsonResponse(array(
                        'files' => array(
                            array(
                                'hidden' => 'nazwapliku',
                                'webPath' => 'webPath',
                                'data' => $request->files->all()
                            )
                        )
                    ));
                }
            }

        }

        $view = $form->createView();

        foreach ($view['comments'] as &$c) {
            /* @var $c FormView */
//            niechginie($c->vars['value']->getWebPath());
        }
//        niechginie($form->createView()->vars);

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