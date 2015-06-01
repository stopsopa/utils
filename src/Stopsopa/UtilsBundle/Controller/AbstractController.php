<?php

namespace Stopsopa\UtilsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Stopsopa\UtilsBundle\Lib\Json\Conditionally\Json;
use Stopsopa\UtilsBundle\Lib\Response;
use Stopsopa\UtilsBundle\Lib\UtilArray;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractController extends Controller {
    /**
     *
     * @param Request $request
     * @param type $msg
     *
     *
//    {% for flashMessage in app.session.flashbag.get('notice') %}
//        <div class="flash-message">
//            <em>Notice</em>: {{ flashMessage }}
//        </div>
//    {% endfor %}
     */
    protected function setNotification(Request $request, $msg) {
        $request->getSession()->getFlashBag()->set('notice', $msg);

        return $this;
    }

    /**
     * Security Context mozna uzyc w sprawdzaniu uprawnien
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurity() {
        return $this->get('security.context');
    }
    /**
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSession() {
        return $this->get('session');
    }
    /**
     *
     * @param type $type
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager($type = 'default') {
        return $this->get("doctrine.orm.{$type}_entity_manager");
    }
    /**
     *
     * @param type $type
     * @return \Doctrine\DBAL\Connection
     */
    protected function getDbal($type = 'default') {
        return $this->getEntityManager($type)->getConnection();
    }
    /**
     *
     * @param type $entity
     * @param type $bundle
     * @param type $entityManager
     * @return type
     */
    protected function getRepository($entity, $bundle = null, $entityManager = 'default') {

        if (strchr($entity, ':'))  // simon
            return $this->getEntityManager($entityManager)->getRepository($entity);

        if ($bundle === null)
          $bundle = $this->getBundleName();

        return $this->getEntityManager($entityManager)->getRepository($bundle . ':' . $entity);
    }
    /**
     * @return string
     */
    public function getBundleName($asset = false) {
      $b = preg_replace('#^(.*?Bundle\\\\).*$#i', '$1', get_class($this));
      $b = str_replace('\\', '', $b);

      if ($asset)
        return substr(strtolower($b),0,-6) ;

      return $b;
    }
    /**
     * Służy do szybszego formułowania odpowiedzi json z poziomu kontrolera,
     * inaczej: ma na celu wyeliminowanie ciągłego powtarzania tworzenia i wypełniania
     * obiektu Response, bo trochę jest przy tym czynności
     *
     * Wyrzuciłem do serwisu aby można było używać tego nie tylko w kontrolerze
     * @param array $array
     * @param array $error
     * @param Response $response
     * @return Response
     */
    public static function getJsonResponse(array $array = array(), $response = null) {
        $response or $response = new Response();

        if ($response instanceof Response) {
            /* @var $response Response */
            $response = $response->extendJson($array);
        }
        else {
            $data = Json::decode($response->getContent());

            if (!@is_array($data))
                $data = array();

            if (@is_array($array))
                $data = UtilArray::arrayMergeRecursiveDistinct($data, $array);

            $response->setContent(Json::encode($data));
        }

        $response->headers->set('content-type', 'application/json; charset=utf-8');

        return $response;
    }
    /**
     * @return
     */
    protected function getToken() {
        return $this->getSecurity()->getToken();
    }
    /**
     * Uzytkownik aktualnie zalogowany
     * @return User|null
     */
    public function getUser() {
      $user = $this->getToken()->getUser();

      if(is_object($user))
            return $user;

      return null;
    }
}