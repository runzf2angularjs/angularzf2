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

namespace Oft\Http;

use Oft\Http\RequestInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request implements RequestInterface
{

    /**
     * @var SymfonyRequest
     */
    protected $request;

    /**
     * Initialisation
     *
     * @param SymfonyRequest $request
     */
    public function __construct(SymfonyRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @return SymfonyRequest
     */
    public function getRequestObject()
    {
        return $this->request;
    }

    /**
     * Retourne la baseUrl
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->request->getBaseUrl();
    }

    /**
     * Retourne la basePath
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->request->getBasePath();
    }

    public function getPathInfo()
    {
        return $this->request->getPathInfo();
    }

    /**
     * Retourne la valeur pour la clef $name dans la superglobale $_SERVER
     *
     * Si $name est null, retourne l'ensemble du tableau.
     * Si $name n'existe pas, $default sera retourné.
     * Si $deep est VRAI, un nom tel que "foo[bar]" sera recherché dans le tableau des paramètres.
     *
     * @param string $name
     * @param mixed $default
     * @param bool $deep
     * @return mixed
     */
    public function getFromServer($name = null, $default = null, $deep = false)
    {
        if ($name === null) {
            return $this->request->server->all();
        }

        return $this->request->server->get($name, $default, $deep);
    }

    /**
     * Retourne la valeur pour la clef $name dans la superglobale $_GET
     *
     * Si $name est null, retourne l'ensemble du tableau.
     * Si $name n'existe pas, $default sera retourné.
     * Si $deep est VRAI, un nom tel que "foo[bar]" sera recherché dans le tableau des paramètres.
     *
     * @param string $name
     * @param mixed $default
     * @param bool $deep
     * @return mixed
     */
    public function getFromQuery($name = null, $default = null, $deep = false)
    {
        if ($name === null) {
            return $this->request->query->all();
        }

        return $this->request->query->get($name, $default, $deep);
    }

    /**
     * Retourne la valeur pour la clef $name dans la superglobale $_POST
     *
     * Si $name est null, retourne l'ensemble du tableau.
     * Si $name n'existe pas, $default sera retourné.
     * Si $deep est VRAI, un nom tel que "foo[bar]" sera recherché dans le tableau des paramètres.
     *
     * @param string $name
     * @param mixed $default
     * @param bool $deep
     * @return mixed
     */
    public function getFromPost($name = null, $default = null, $deep = false)
    {
        if ($name === null) {
            return $this->request->request->all();
        }

        return $this->request->request->get($name, $default, $deep);
    }

    /**
     * Retourne la valeur pour la clef $name dans la superglobale $_COOKIE
     *
     * Si $name est null, retourne l'ensemble du tableau.
     * Si $name n'existe pas, $default sera retourné.
     * Si $deep est VRAI, un nom tel que "foo[bar]" sera recherché dans le tableau des paramètres
     *
     * @param string $name
     * @param mixed $default
     * @param bool $deep
     * @return mixed
     */
    public function getFromCookies($name = null, $default = null, $deep = false)
    {
        if ($name === null) {
            return $this->request->cookies->all();
        }

        return $this->request->cookies->get($name, $default, $deep);
    }

    /**
     * Retourne la valeur pour la clef $name parmi $_SERVER['HTTP_*']
     *
     * $name ne doit pas être précédée du préfixe "HTTP_".
     * Si $name est null, retourne l'ensemble du tableau.
     * Si $name n'existe pas, $default sera retourné.
     * Si $first est vrai, retourne la première valeur trouvée
     *
     * @param array $array
     * @param string $name
     * @param mixed $default
     * @param bool $first
     * @return mixed
     */
    public function getFromHeaders($name = null, $default = null, $first = true)
    {
        if ($name === null) {
            return $this->request->headers->all();
        }

        return $this->request->headers->get($name, $default, $first);
    }

    /**
     * Retourne la méthode HTTP
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->request->getMethod();
    }

    /**
     * Retourne la requête URI
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $this->request->getRequestUri();
    }

    /**
     * Retourne VRAI si la méthode HTTP est POST
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->request->isMethod('post');
    }

    /**
     * Retourne VRAI si la méthode HTTP donnée est celle utilisée
     *
     * @param string $method
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->request->isMethod($method);
    }

    /**
     * Retourne VRAI si la requête est sécurisée (HTTPS)
     *
     * @return bool
     */
    public function isHttps()
    {
        return $this->request->isSecure();
    }

    /**
     * Retourne VRAI si la requête est de type AJAX
     *
     * @return bool
     */
    public function isXmlHttpRequest()
    {
        return $this->request->isXmlHttpRequest();
    }
    
    /**
     * Retourne la langue utilisé par le navigateur
     * Possibilité de retourner seulement la langue si elle est contenue dans le tableau passé en paramètres 
     * 
     * @param array $localesArray
     * @return string
     */
    public function getPreferredLanguage($localesArray = null)
    {
        return $this->request->getPreferredLanguage($localesArray);
    }

    public function getContent()
    {
        return $this->request->getContent();
    }

    public function getQueryString()
    {
        return $this->request->getQueryString();
    }
}
