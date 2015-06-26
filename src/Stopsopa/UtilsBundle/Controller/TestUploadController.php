<?php

namespace Stopsopa\UtilsBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stopsopa\UtilsBundle\Lib\Request;
use Stopsopa\UtilsBundle\Entity\UserManager;
use Stopsopa\UtilsBundle\Entity\CommentManager;
use Stopsopa\UtilsBundle\Form\UserType;
use Symfony\Component\Form\FormView;
use Stopsopa\UtilsBundle\Entity\User;
use DateTime;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormError;
use Stopsopa\UtilsBundle\EventListener\UploadSubscriber;
use Stopsopa\UtilsBundle\Entity\Comment;
use Stopsopa\UtilsBundle\Lib\UtilFormAccessor;

/**
 * @Route("/test/upload")

routing.yml

stopsopautils:
    resource: "@ StopsopaUtilsBundle/Controller/"
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

            $c = new Comment();
            $c->setPath('/testpath1');
            $entity->addComment($c);
            $c = new Comment();
            $c->setPath('/testpath2');
            $entity->addComment($c);

        $form = $this->createForm($type, $entity, array(
            'action' => $this->generateUrl($request),
        ));

        if ($request->files->count()) {

            $form->handleRequest($request);

            niechginie($request->files->all(), null, 4);
        }


        if ($request->isPost()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $man->update($entity);

                $this->setNotification($request, 'Created');

                return $this->redirect($request);
            }
        }

        $d = UtilFormAccessor::setValue($form, 'user[comments][1][path]', 'test');



        return $this->render('StopsopaUtilsBundle:upload:create.html.twig', array(
            'form' => $form->createView()
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

        $form = $this->createForm($type, $entity, array(
            'action' => $this->generateUrl($request),
        ));

        if ($request->isPost()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $man->update($entity);

                $this->setNotification($request, 'Edited');

                return $this->redirect($request);
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