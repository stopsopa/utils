<?php

namespace Stopsopa\UtilsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stopsopa\UtilsBundle\Entity\Comment;
use Stopsopa\UtilsBundle\Entity\User;
use Stopsopa\UtilsBundle\Entity\UserManager;
use Stopsopa\UtilsBundle\Form\UserType;
use Stopsopa\UtilsBundle\Lib\FileProcessors\CommentFileProcessor;
use Stopsopa\UtilsBundle\Lib\FileProcessors\Tools\UploadHelper;
use Stopsopa\UtilsBundle\Lib\FileProcessors\UserFileProcessor;
use Stopsopa\UtilsBundle\Lib\Request;

/**
 * @Route("/test/upload")
 */
class TestUploadController extends AbstractController
{
    /**
     * @Route("", name="test-upload")
     */
    public function upload(Request $request)
    {

        /* @var $man UserManager */
        $man = $this->get(UserManager::SERVICE);

        return $this->render('StopsopaUtilsBundle:upload:index.html.twig', array(
            'users' => $man->findAll(),
        ));
    }
    /**
     * @Route("/create", name="test-upload-create")
     */
    public function createAction(Request $request)
    {

        /* @var $man UserManager */
        $man = $this->get(UserManager::SERVICE);

        /* @var $entity User */
        $entity = $man->createEntity();

        $type = new UserType();

//            $c = new Comment();
//            $c->setPath('/testpath1');
//            $entity->addComment($c);
//            $c = new Comment();
//            $c->setPath('/testpath2');
//            $entity->addComment($c);

        $form = $this->createForm($type, $entity, array(
            'action' => $this->generateUrl($request),
        ));

        $uploadhelper = new UploadHelper($request);
        $uploadhelper->addProcessor(new UserFileProcessor());
        $uploadhelper->addProcessor(new CommentFileProcessor());

        if ($request->isPost()) {

            // później można wykombinować jakoś żeby sprzęgnąć ewentualne błędy z formem
            if ($uploadhelper->countFiles()) {
                return $this->getJsonResponse($uploadhelper->handle());
            }

            $form->handleRequest($request);

            if ($form->isValid()) { // obsługa całego formularza

                $man->update($entity);

                $response = $uploadhelper->move();

                $this->setNotification($request, 'Created');

                return $this->redirectToRoute('test-upload-edit', array(
                    'id' => $entity->getId(),
                ));
            }
        }

        return $this->render('StopsopaUtilsBundle:upload:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    /**
     * @Route("/edit/{id}", name="test-upload-edit", requirements={"id"="\d+"})
     */
    public function editAction(Request $request, $id)
    {

        /* @var $man UserManager */
        $man = $this->get(UserManager::SERVICE);

        /* @var $entity User */
        $entity = $man->findOrThrow($id);

        $type = new UserType();

        $form = $this->createForm($type, $entity, array(
            'action' => $this->generateUrl($request),
        ));

        $uploadhelper = new UploadHelper($request);
        $uploadhelper->addProcessor(new UserFileProcessor());
        $uploadhelper->addProcessor(new CommentFileProcessor());

        if ($request->isPost()) {

            // później można wykombinować jakoś żeby sprzęgnąć ewentualne błędy z formem
            if ($uploadhelper->countFiles()) {
                return $this->getJsonResponse($uploadhelper->handle());
            }

            $form->handleRequest($request);

            if ($form->isValid()) {
                $man->update($entity);

                $response = $uploadhelper->move();

                $this->setNotification($request, 'Edited');

                return $this->redirect($request);
            }
        }

        return $this->render('StopsopaUtilsBundle:upload:edit.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * @Route("/delete/{id}", name="test-upload-delete", requirements={"id"="\d+"})
     */
    public function deleteAction(Request $request, $id)
    {

        /* @var $man UserManager */
        $man = $this->get(UserManager::SERVICE);

        /* @var $entity User */
        $entity = $man->findOrThrow($id);

        $dbal = $man->getDbal();
        $dbal->beginTransaction();

        $man->remove($entity);

//        $dbal->rollBack();
        $dbal->commit();

        $uploadhelper = new UploadHelper($request);
        $uploadhelper->addProcessor(new UserFileProcessor());
        $uploadhelper->addProcessor(new CommentFileProcessor());

        foreach ($entity->getComments() as $c) {
            $uploadhelper->delete($c);
        }
        $uploadhelper->delete($entity);

        $this->setNotification($request, 'Deleted');

        return $this->redirect($request->headers->get('referer'));
    }
}
