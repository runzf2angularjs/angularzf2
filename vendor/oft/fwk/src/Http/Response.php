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

use DateTime;
use Oft\Http\ResponseInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response implements ResponseInterface
{

    /**
     * @var SymfonyResponse
     */
    protected $response;

    /**
     * Flag, réponse envoyée ou non
     *
     * @var bool
     */
    protected $sent = false;

    /**
     * Initialisation
     *
     * @param SymfonyResponse $response
     */
    public function __construct(SymfonyResponse $response)
    {
        $this->response = $response;
    }

    /**
     * @return SymfonyResponse
     */
    public function getResponseObject()
    {
        return $this->response;
    }

    /**
     * Définit le code HTTP
     *
     * @param int $code Code
     * @param string $phrase Texte associé au code
     */
    public function setStatusCode($code, $phrase = null)
    {
        $this->response->setStatusCode($code, $phrase);

        return $this;
    }

    /**
     * Retourne le code HTTP
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * Définit l'entête Content-Type
     *
     * @param string $type
     * @param string $charset
     */
    public function setContentType($type, $charset = 'UTF-8')
    {
        $this->response->headers->set('Content-Type', $type);
        $this->response->setCharset($charset);

        return $this;
    }

    /**
     * Récupère l'entête Content-Type
     *
     * @param string $type
     * @param string $charset
     */
    public function getContentType()
    {
        return $this->response->headers->get('Content-Type', 'text/html');
    }

    /**
     * Envoie un cookie
     *
     * @param string $name Nom du cookie
     * @param string $value Valeur du cookie
     * @param string|int|DateTime $expire Date/heure d'expiration, 0 signifie "lorsque le navigateur sera fermé"
     * @param string $path Chemin pour lequel le cookie sera disponible
     * @param string $domain Domaine pour lequel le cookie sera disponible
     * @param bool $secure Disponibilité du cookie seulement en HTTPS
     * @param bool $httpOnly Visibilité du cookie côté client
     */
    public function setCookie($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true)
    {
        $cookie = new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        $this->response->headers->setCookie($cookie);

        return $this;
    }

    /**
     * Supprime un cookie
     *
     * @param string $name Nom du cookie
     * @param string $path Chemin pour lequel le cookie sera disponible
     * @param string $domain Domaine pour lequel le cookie sera disponible
     */
    public function deleteCookie($name, $path = '/', $domain = null)
    {
        $this->response->headers->removeCookie($name, $path, $domain);
    }

    /**
     * Définit le contenu de la réponse
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->response->setContent($content);

        return $this;
    }

    /**
     * Retourne le contenu de la réponse
     *
     * @return string
     */
    public function getContent()
    {
        return $this->response->getContent();
    }

    /**
     * Ajoute le contenu donné à la réponse
     *
     * @param string $content
     */
    public function addContent($content)
    {
        $this->setContent($this->response->getContent() . $content);
    }


    public function prependContent($content)
    {
        $this->response->setContent($content . $this->response->getContent());
    }

    /**
     * Ajout d'une entête à la réponse
     *
     * @param string $key Nom de l'entête
     * @param mixed $values Valeur(s) de l'entête
     */
    public function setHeader($key, $values, $replace = true)
    {
        $this->response->headers->set($key, $values, $replace);

        return $this;
    }

    public function addHeaders(array $headers)
    {
        $this->response->headers->add($headers);

        return $this;
    }

    /**
     * Envoi la réponse
     */
    public function send()
    {
        $this->response->send();
    }

}
