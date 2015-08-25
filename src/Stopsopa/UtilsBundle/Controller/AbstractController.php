<?php

namespace Stopsopa\UtilsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Stopsopa\UtilsBundle\Lib\Json\Conditionally\Json;
use Stopsopa\UtilsBundle\Lib\Response;
use Symfony\Component\HttpFoundation\Response as SfResponse;
use Stopsopa\UtilsBundle\Lib\UtilArray;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Stopsopa\UtilsBundle\Lib\AbstractApp;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilArgs;

abstract class AbstractController extends Controller
{
    /**
     * @param Request $request
     * @param type    $msg
     */
    protected function setNotification(Request $request, $msg)
    {
        $request->getSession()->getFlashBag()->set('notice', $msg);

        return $this;
    }
    protected function setError(Request $request, $msg)
    {
        $request->getSession()->getFlashBag()->set('error', $msg);

        return $this;
    }

    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $name = $route;
        if ($route instanceof Request) {
            $name = $route->get('_route', 'error_no_route_specified');
        }

        try {
            return parent::generateUrl($name, $parameters, $referenceType);
        } catch (MissingMandatoryParametersException $ex) {
            if ($route instanceof Request) {
                return $route->getRequestUri();
            }
            throw $ex;
        }
    }
    public function redirect($url, $status = 302)
    {
        if ($url instanceof Request) {
            $url = $this->generateUrl($url);
        }

        return parent::redirect($url, $status);
    }
    public function redirectToRoute($route, array $parameters = array(), $status = 302)
    {
        if ($route instanceof Request) {
            $route = $route->get('_route', 'error_no_route_specified');
        }

        return parent::redirectToRoute($route, $parameters, $status);
    }

    /**
     * Security Context mozna uzyc w sprawdzaniu uprawnien.
     *
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurity()
    {
        return $this->get('security.context');
    }
    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSession()
    {
        return $this->get('session');
    }
    /**
     * @param type $type
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager($type = 'default')
    {
        return $this->get("doctrine.orm.{$type}_entity_manager");
    }
    /**
     * @param type $type
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function getDbal($type = 'default')
    {
        return $this->get("doctrine.dbal.{$type}_connection");
    }
    /**
     * @param type $entity
     * @param type $bundle
     * @param type $entityManager
     *
     * @return type
     */
    protected function getRepository($entity, $bundle = null, $entityManager = 'default')
    {
        if (strchr($entity, ':')) {  // simon
            return $this->getEntityManager($entityManager)->getRepository($entity);
        }

        if ($bundle === null) {
            $bundle = $this->getBundleName();
        }

        return $this->getEntityManager($entityManager)->getRepository($bundle.':'.$entity);
    }
    /**
     * @return string
     */
    public function getBundleName($asset = false)
    {
        $b = preg_replace('#^(.*?Bundle\\\\).*$#i', '$1', get_class($this));
        $b = str_replace('\\', '', $b);

        if ($asset) {
            return substr(strtolower($b), 0, -6);
        }

        return $b;
    }
    /**
     * Służy do szybszego formułowania odpowiedzi json z poziomu kontrolera,
     * inaczej: ma na celu wyeliminowanie ciągłego powtarzania tworzenia i wypełniania
     * obiektu Response, bo trochę jest przy tym czynności.
     *
     * Wyrzuciłem do serwisu aby można było używać tego nie tylko w kontrolerze
     *
     * @param array    $array
     * @param array    $error
     * @param Response $response
     *
     * @return Response
     */
    public static function getJsonResponse(array $array = array(), $response = null)
    {
        $response or $response = new Response();

        if ($response instanceof Response) {
            /* @var $response Response */
            $response = $response->extendJson($array);
        } else {
            $data = Json::decode($response->getContent());

            if (!@is_array($data)) {
                $data = array();
            }

            if (@is_array($array)) {
                $data = UtilArray::arrayMergeRecursiveDistinct($data, $array);
            }

            $response->setContent(Json::encode($data));
        }

        $response->headers->set('content-type', 'application/json; charset=utf-8');

        return $response;
    }
    /**
     * @return
     */
    protected function getToken()
    {
        return $this->getSecurity()->getToken();
    }
    /**
     * Uzytkownik aktualnie zalogowany.
     *
     * @return User|null
     */
    public function getUser()
    {
        $token = $this->getToken();

        if (!$token) {
            return false;
        }

        $user = $token->getUser();

        if (is_object($user)) {
            return $user;
        }

        return;
    }
    public function getUserOrThrow() {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException("User not found in session");
        }
        return $user;
    }

    /**
     * Możliwe że później trzeba będzie rozbudować tą metodę o obsługę
     * zagnieżdżonych formularzy.
     *
     * @param bool $wrapped - wsadza w dodatkowy poziom tablicy z kluczem którego spodziewa się skrypt obsługi formularzy
     * @param Form $entity
     */
    public function getErrors(Form $form, $wrapped = false)
    {
        $view = $form->createView();
        $errors = $this->_getChildrenErrors($view->children);
//        niechginie($view,2);
        if ($wrapped) {
            return array(
                is_string($wrapped) ? $wrapped : 'error' => $errors,
            );
        }

        return $errors;
    }
    protected function _getChildrenErrors($list)
    {
        $errors = array();

        if (@count($list)) {
            foreach ($list as $k => $formview) {
                /* @var $formview FormView */
                $vars = $formview->vars;
                if (@count($vars['errors'])) {
                    $ee = array();
                    foreach ($vars['errors'] as $e) {
                        /* @var $e FormError */
                        $ee[] = $e->getMessage();
                    }
                    $errors[$vars['id']] = $ee;
                }
                $errors = $this->_merget($errors, $this->_getChildrenErrors($formview->children));
            }
        }

        return $errors;
    }
    protected function _merget($a1, $a2)
    {
        foreach ($a2 as $k => $d) {
            if (@is_array($a1[$k])) {
                foreach ($d as $k1 => $d1) {
                    $a1[$k][] = $d1;
                }
            } else {
                $a1[$k] = $d;
            }
        }

        return $a1;
    }
    /**
     * Teraz można wywołać w dowolny sposób:
//     * $this->render();
//     * $this->render(array(...));
//     * $this->render('index.html.twig');
//     * $this->render('Folder:index.html.twig');
//     * $this->render('Folder\Subfolder:index.html.twig');
//     * $this->render($response);
//     * $this->render(array(...), $response);
//     * $this->render($response, array(...));
//     * $this->render('index.html.twig');
//     * $this->render('Folder\Subfolder:index.html.twig');
//     * $this->render('index.html.twig', array(...));
//     * $this->render('Folder\Subfolder:index.html.twig', $response);
//     * $this->render('index.html.twig', array(...), $response);
//     * $this->render('index.html.twig', $response, array(...));.
     *
     * @param SfResponse $view
     * @param array      $parameters
     * @param SfResponse $response
     *
     * @return type
     */
    public function rend()
    {
        $a = new UtilArgs(func_get_args());

        $view = $a->getFirst(UtilArgs::STRING);

        $parameters = $a->getFirst(UtilArgs::ARR, array());

        $response = $a->getFirst('Symfony\Component\HttpFoundation\Response', null);

        if (!is_string($view) || !$view) {
            $rparams = $this->get('request')->attributes->get('_controller');

            if (strpos($rparams, '\\Controller\\')) {
                preg_match('#^(?:.*?)\\\\Controller\\\\(.*?)Controller::(.*?)(?:Action)?$#', $rparams, $matches);
                $view = $this->getBundleName().':'.$matches[1].':'.$matches[2].'.html.twig';
            }
            else {
                $view = $rparams.'.html.twig';
            }
        }

        switch (substr_count($view, ':')) {
            case 2:
                break;
            case 1:
                $view = $this->getBundleName().':'.$view;
                break;
            default:
                $rparams = $this->get('request')->attributes->get('_controller');

                if (strpos($rparams, '\\Controller\\')) {
                    preg_match('#^(?:.*?)\\\\Controller\\\\(.*?)Controller::(.*?)(?:Action)?$#', $rparams, $matches);
                    $view = $this->getBundleName().':'.$matches[1].':'.$view;
                }
                else {
                    $view = $rparams.'.html.twig';
                }
                break;
        }
//        nieginie($view);

        return $this->render($view, $parameters, $response);
    }
    public function getRootDir($bundlepath = false)
    {
        return AbstractApp::getRootDir($bundlepath);
    }
}
