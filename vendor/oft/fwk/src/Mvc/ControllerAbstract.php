<?php
/**
 * Copyright (C) 2015 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * If you are Orange employee you shall use this software in accordance with
 * the Orange Source Charter (http://opensource.itn.ftgroup/index.php/Orange_Source).
 */

namespace Oft\Mvc;

use DomainException;
use Oft\Acl\Acl;
use Oft\Auth\Identity;
use Oft\Http\Request;
use Oft\Http\Response;
use Oft\Http\Session;
use Oft\Mvc\Application;
use Oft\Mvc\Exception\ForwardException;
use Oft\Mvc\Exception\RedirectException;
use Oft\Mvc\Helper\Json;
use Oft\View\Helper\FlashMessenger;
use Oft\View\Model;
use RuntimeException;
use Zend\InputFilter\InputFilter;

/**
 * @method bool isDebug() Indique si l'application est en mode debug ou pas
 * @method Identity getCurrentIdentity() Retourne l'identité de l'utilisateur connecté
 * @method bool isPost() Indique si la HTTP méthode POST est utilisée
 * @method ControllerAbstract setLayoutTemplate(string $layoutTemplateName, string $layoutTemplatePath=null) Défini le nom du layout
 * @method ControllerAbstract setTemplate(string $viewTemplate) Template de vue à utiliser
 * @method ControllerAbstract setRenderLayout(bool $shouldRender = true) Défini si le layout doit être rendu ou pas
 * @method ControllerAbstract setRenderView(bool $shouldRender = true) Défini si la vue doit être rendue ou pas
 * @method ControllerAbstract disableRendering($disableView = true, $disableLayout = true) Désactive le rendu
 * @method string smartUrlFromRoute(string $routeName, array $params = array()) Génére une url en fonction d'une route nommée
 * @method string smartUrl(string $action=null, string $controller=null, string $module=null, array $params=array(), string $routeName=null) Génére une URL en fonction des paramètres
 * @method Application breadcrumb(string $text, string $link) Ajoute un lien au fil d'ariane
 */
class ControllerAbstract
{

    /**
     * Message de type "succès"
     *
     * @const string
     */
    const SUCCESS = 'success';

    /**
     * Message de type "information"
     *
     * @const string
     */
    const INFO = 'info';

    /**
     * Message de type "avertissement"
     *
     * @const string
     */
    const WARNING = 'warning';

    /**
     * Message de type "erreur" ou "danger"
     *
     * @const string
     */
    const DANGER = 'danger';

    /**
     * Requête HTTP
     *
     * @var Request
     */
    protected $request;

    /**
     * Réponse HTTP
     *
     * @var Response
     */
    protected $response;

    /**
     * Session PHP
     *
     * @var Session
     */
    protected $session;

    /**
     * Modèle de vue
     *
     * @var Model
     */
    protected $viewModel;

    /**
     * Conteneur d'application
     *
     * @var Application
     */
    protected $app;

    /**
     * Filtrage et validation des données GET, POST et COOKIE
     * 
     * @var InputFilter
     */
    protected $inputFilter;

    /**
     * Règles de validation et de filtrage a appliquer sur les paramètre de la query string.
     * 
     * @var array
     */
    protected $inputFilterRules = array();

    /**
     * Méthode d'initialisation exécutée à la construction
     */
    public function init()
    {

    }

    /**
     * Définit le conteneur d'application
     *
     * Définit également, à partir du conteneur d'application :
     * - L'objet Requête HTTP
     * - L'objet Réponse HTTP
     * - Le gestionnaire de services
     *
     * @param Application $app Conteneur d'application
     * @return void
     */
    public function setApplication(Application $app)
    {
        $this->app = $app;
        $this->request = $app->http->request;
        $this->response = $app->http->response;
        $this->session = $app->http->session;
    }

    /**
     * Définit le conteneur de variables
     *
     * @param Model $viewModel
     */
    public function setViewModel(Model $viewModel)
    {
        $this->viewModel = $viewModel;
    }

    /**
     * Lance une redirection HTTP sur la base de composantes de route
     *
     * @param string $action Action cible
     * @param string $controller Contrôleur cible
     * @param string $module Module cible
     * @param array $params Paramètres de la route
     * @param string $routeName Nom de la route
     * @throws RedirectException
     */
    public function redirect($action = null, $controller = null, $module = null, array $params = array(), $routeName = null, $queryString = array())
    {
        $url = $this->app->view->smartUrl($action, $controller, $module, $params, $routeName);

        throw new RedirectException($url);
    }

    /**
     * Lance une redirection HTTP sur la base d'une URL
     *
     * @param string $url URL cible
     * @throws RedirectException
     */
    public function redirectToUrl($url = null)
    {
        throw new RedirectException($url);
    }

    /**
     * Lance une redirection HTTP vers une route configurée
     *
     * @param string $routeName Nom de la route
     * @param array $params Paramètres de la route
     * @throws RedirectException
     */
    public function redirectToRoute($routeName = null, array $params = array())
    {
        $url = $this->app->view->smartUrlFromRoute($routeName, $params);
        
        throw new RedirectException($url);
    }

    /**
     * Redirige la requête vers une route différente
     *
     * @param array $route Route ciblée
     * @param array $params Paramètres de la route ciblée
     * @throws ForwardException
     */
    public function forward(array $route, array $params = array())
    {
        throw new ForwardException($route, $params);
    }

    /**
     * Ajoute un message flash qui sera affiché au prochain affichage du layout
     *
     * @param string $message Texte du message
     * @param string $type Type du message
     * @return void
     */
    public function flashMessage($message, $type = self::INFO)
    {
        FlashMessenger::getMessagesContainer()->append(array(
            $type,
            $message
        ));
    }

    /**
     * Vérifie si l'utilisateur a accès à la ressource MVC
     *
     * @param  string|array $action
     * @param  string       $controller
     * @param  string       $module
     * @return boolean
     */
    public function hasAccessTo($action = null, $controller = null, $module = null)
    {
        /* @var $identity Identity */
        $identity = $this->app->identity->get();

        /* @var $acl Acl */
        $acl = $this->app->get('Acl');

        $route = $this->app->route->current;

        if (is_array($action)) {
            $route = array_merge($route, $action);
        } else {
            $route = $this->app->route->current;
            if ($action !== null) {
                $route['action'] = $action;
            }
            if ($controller !== null) {
                $route['controller'] = $controller;
            }
            if ($module !== null) {
                $route['module'] = $module;
            }
        }

        return $acl->isMvcAllowed($route, $identity);
    }

    public function getInputFilterRules()
    {
        return $this->inputFilterRules;
    }

    public function getInputFilter()
    {
        if ($this->inputFilter === null) {
            $inputFilter = new InputFilter();

            foreach ($this->getInputFilterRules() as $name => $rules) {
                $inputFilter->add($rules, $name);
            }

            $inputFilter->setData(array_merge($_COOKIE, $_POST, $_GET));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function hasParam($name)
    {
        return $this->getInputFilter()->has($name);
    }

    public function getParam($name, $default = null)
    {
        try {
            $inputFilter = $this->getInputFilter()->get($name);
        } catch (\Exception $e) {
            if ($this->isDebug()) {
                $this->flashMessage($e->getMessage(), self::WARNING);
            }
            return $default;
        }

        $rawValue = $this->inputFilter->getRawValue($name);
        if ($rawValue !== '0' && $rawValue !== 0 && empty($rawValue)) {
            return $default;
        }

        if (!$inputFilter->isValid()) {
            oft_error('Erreur de validation', $inputFilter->getMessages());
            if ($this->isDebug()) {
                throw new DomainException("Erreur de validation '$name' : "
                    . implode(', ', $inputFilter->getMessages()));
            }
            throw new DomainException("Erreur de validation des paramètres");
        }

        return $inputFilter->getValue();
    }

    public function sendJson($json, array $options = array())
    {
        return Json::send($json, $options);
    }

    public function send($content, $contentType = null, array $headers = array(), $statusCode = 200)
    {
        if ($contentType != null) {
            $headers['Content-Type'] = $contentType;
        }
        throw new Exception\HttpException($statusCode, $headers, $content);
    }

    /**
     * Racourcis vers des fonctions assumées par d'autres composants
     *
     * @param string $method
     * @param array $args
     *
     * @return ControllerAbstract
     * 
     * @throws Exception\NotFoundException
     * @throws RuntimeException
     */
    public function __call($method, $args)
    {
        switch ($method) {
            case 'isDebug':
                return $this->app->isDebug;

            case 'getCurrentIdentity':
                return $this->app->identity->get();

            case 'isPost':
                return $this->request->isPost();

            case 'setLayoutTemplate':
                $layoutTemplateName = $args[0];
                $layoutTemplatePath = isset($args[1]) ? $args[1] : null;
                $this->app->renderOptions->setLayoutTemplate($layoutTemplateName, $layoutTemplatePath);

                return $this;

            case 'setTemplate':
                $viewTemplate = $args[0];
                $this->app->renderOptions->setTemplate($viewTemplate);

                return $this;

            case 'setRenderLayout':
                $shouldRender = (bool)$args[0];
                $this->app->renderOptions->setRenderLayout($shouldRender);

                return $this;

            case 'setRenderView':
                $shouldRender = (bool)$args[0];
                $this->app->renderOptions->setRenderView($shouldRender);

                return $this;

            case 'disableRendering':
                $disableView = isset($args[0]) ? (bool)$args[0] : true;
                $disableLayout = isset($args[1]) ? (bool)$args[1] : true;

                $this->app->renderOptions->setRenderLayout(!$disableLayout)
                    ->setRenderView(!$disableView);

                return $this;

            case 'smartUrlFromRoute':
                $route = isset($args[0]) ? $args[0] : null;
                $params = isset($args[1]) ? $args[1] : array();

                return $this->app->view->smartUrlFromRoute($route, $params);

            case 'smartUrl':
                $action = isset($args[0]) ? $args[0] : null;
                $controller = isset($args[1]) ? $args[1] : null;
                $module = isset($args[2]) ? $args[2] : null;
                $params = isset($args[3]) ? $args[3] : array();
                $routeName = isset($args[4]) ? $args[4] : null;

                return $this->app->view->smartUrl($action, $controller, $module, $params, $routeName);
                
            case 'breadcrumb':
                $text = isset($args[0]) ? $args[0] : null;
                $link = isset($args[1]) ? $args[1] : null;
                
                $this->app->view->breadcrumb($text, $link);

                return $this;
        }

        if (substr($method, -6) == 'Action') {
            throw new Exception\NotFoundException();
        }

        throw new RuntimeException("method does not exists");
    }
}
