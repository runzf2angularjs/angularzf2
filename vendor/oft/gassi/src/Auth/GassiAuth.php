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

namespace Oft\Gassi\Auth;

use DomainException;
use Oft\Auth\AuthInterface;
use Oft\Auth\Identity;
use Oft\Auth\IdentityStore\IdentityStoreInterface;
use Oft\Http\Request;
use Oft\Mvc\Application;

/**
 * Composant d'authentification GASSI
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class GassiAuth implements AuthInterface
{

    /**
     * @var IdentityStoreInterface
     */
    protected $store;

    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * Construction
     * 
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->store = $app->get('IdentityStore');
        $this->httpRequest = $app->http->request;
    }

    /**
     * Retourne le formulaire associé à ce mode d'authentification
     *
     * @return null
     */
    public function getForm()
    {
        return null;
    }

    /**
     * Retourne l'objet Identity à la suite de l'authentification ou lève une exception
     *
     * @return Identity
     * @throws DomainException
     */
    public function authenticate()
    {
        // Présence de l'entête HTTP_SM_AUTHTYPE
        $authType = $this->httpRequest->getFromServer('HTTP_SM_AUTHTYPE', false);
        if (!$authType || strtolower($authType) != 'form') {
            throw new DomainException("Authentification impossible");
        }

        // Récupération du CUID
        $username = $this->httpRequest->getFromServer('HTTP_SM_UNIVERSALID', false);
        if ($username === false) {
            throw new DomainException("Authentification impossible");
        }
        
        return $this->store->getIdentity($username);
    }
}
