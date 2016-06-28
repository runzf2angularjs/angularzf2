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

namespace Oft\Gassi\Auth\IdentityStore;

use Oft\Auth\Identity;
use Oft\Mvc\Application;
use RuntimeException;

/**
 * Composant complémentaire de récupération des rôles utilisateurs en base de données
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class GassiDb extends Gassi
{

    /**
     * Entité de l'utilisateur
     *
     * @var string
     */
    protected $userEntityClassName = 'Oft\Entity\UserEntity';

    /**
     * Construction
     * 
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->db = $app->db;
    }

    /**
     * Définition de l'entité de l'utilisateur
     *
     * @param string $className
     */
    public function setUserEntityClassName($className)
    {
        $this->userEntityClassName = $className;
    }

    /**
     * Retourne un objet Identity à partir des informations collectées
     *
     * @param string $username
     * @return Identity
     * @throws RuntimeException
     */
    public function getIdentity($username)
    {
        $identity = parent::getIdentity($username);

        // Merge groups with Db defined groups
        $entity = new $this->userEntityClassName($this->db);
        $entity->loadByUserName($username);
        $groups = array_merge(
            $identity->groups,
            $entity->getGroups()
        );

        $identity->merge(array('groups' => $groups));

        return $identity;
    }
    
}
