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

use Doctrine\DBAL\Connection;
use Oft\Auth\Identity;
use Oft\Entity\UserEntity;
use Oft\Mvc\Application;

/**
 * Classe de récupération des informations de connexion depuis la base de données
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class DbTable implements IdentityStoreInterface
{

    /**
     * Connection au SGBD
     *
     * @var Connection
     */
    protected $db;

    /**
     * Initialisation
     *
     * @param Application $app
     * @return self
     */
    public function __construct(Application $app)
    {
        $this->db = $app->db;
    }

    /**
     * Retourne les données correspondants à l'utilisateur demandé
     *
     * @param string $username
     * @return Identity
     */
    public function getIdentity($username)
    {
        $entity = new UserEntity($this->db);
        $entity->loadByUserName($username);

        return new Identity($entity->getArrayForIdentity());
    }
}
