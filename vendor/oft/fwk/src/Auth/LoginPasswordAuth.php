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

namespace Oft\Auth;

use Oft\Auth\Form\LoginPasswordForm;
use Oft\Auth\Identity;
use Oft\Auth\IdentityStore\IdentityStoreInterface;
use Oft\Entity\BaseEntity;
use Oft\Mvc\Application;

/**
 * Classe d'authentification via formulaire
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class LoginPasswordAuth implements AuthInterface
{
    /** @var IdentityStoreInterface */
    protected $store;

    /** @var LoginPasswordForm */
    protected $form;

    /**
     * Initialisation
     *
     * @param IdentityStoreInterface Magasin d'identité
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->store = $app->get('IdentityStore');
    }

    /**
     * Retourne le formulaire associé à cette classe d'authentification
     *
     * @return LoginPasswordForm
     */
    public function getForm()
    {
        if ($this->form === null) {
            $entity = new BaseEntity(array('username' => null, 'password' => null));
            $this->form = new LoginPasswordForm();
            $this->form->bind($entity);
        }
        return $this->form;
    }

    /**
     * Do authentication using database.
     *
     * @return Identity
     * @throws \DomainException
     */
    public function authenticate()
    {
        $authData = $this->form->getObject()->getArrayCopy();
        $identity = $this->store->getIdentity($authData['username']);

        if (md5($identity->salt . $authData['password']) !== $identity->password) {
            throw new \DomainException('Mot de passe incorrect');
        }

        if (!$identity->isActive()) {
            throw new \DomainException("L'utilisateur est désactivé");
        }

        unset($identity->salt);
        unset($identity->password);

        return $identity;
    }
}
