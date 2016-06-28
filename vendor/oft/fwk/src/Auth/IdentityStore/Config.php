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

namespace Oft\Auth\IdentityStore;

use Oft\Auth\Identity;
use Oft\Mvc\Application;

/**
 * Classe de récupération des informations de connexion depuis la configuration
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Config implements IdentityStoreInterface
{

    /**
     * Tableau référentiel des utilisateurs
     *
     * @var array
     */
    protected $users;

    /**
     * Initialisation
     *
     * @param Application $app Liste des utilisateurs
     */
    public function __construct(Application $app)
    {
        $this->users = array();
        foreach ($app->config['auth']['store']['params']['users'] as $username => $userData) {
            $this->users[strtolower($username)] = $userData;
        }
    }

    /**
     * Retourne les données correspondants à l'utilisateur demandé
     *
     * @param string $username
     * @return Identity
     */
    public function getIdentity($username)
    {
        $cUsername = strtolower($username);

        if (!array_key_exists($cUsername, $this->users)) {
            throw new \DomainException('Utilisateur inconnu');
        }

        $user = $this->users[$cUsername];

        // Username
        $user['username'] = $cUsername;

        // Groups
        foreach ($user['groups'] as $key => $value) {
            if (is_numeric($key)) {
                unset($user['groups'][$key]);
                $key = strtolower($value);
            }
            $user['groups'][$key] = ucwords($value);
        }
        

        return new Identity($user);
    }

    /**
     * Retourne l'ensemble des identités du référentiel
     *
     * @return array
     */
    public function getIdentityList()
    {
        return $this->users;
    }

}
