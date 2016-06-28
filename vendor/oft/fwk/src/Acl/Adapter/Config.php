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

namespace Oft\Acl\Adapter;

use Oft\Service\ServiceLocatorInterface;

class Config implements AdapterInterface
{

    /**
     * Tableau référentiel des permissions
     *
     * @var array
     */
    protected $permissions = array();

    /**
     * Initialisation
     *
     * @param ServiceLocatorInterface $app
     */
    public function __construct(ServiceLocatorInterface $app)
    {
        $this->permissions = $app->config['acl']['adapter']['params']['permissions'];
    }

    /**
     * Retourne les groupes utilisateurs
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->permissions['roles'];
    }

    /**
     * Retourne les règles d'ouverture ressource(s) / groupe(s)
     *
     * @return array
     */
    public function getAllowed()
    {
        return $this->permissions['allow'];
    }

}
