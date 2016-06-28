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

/**
 * Interface d'adapter Acl
 *
 * @author CC PHP <cdc.php@orange.com>
 */
interface AdapterInterface
{

    /**
     * Initialisation
     */
    public function __construct(ServiceLocatorInterface $app);

    /**
     * Retourne les groupes utilisateurs
     *
     * @return array
     */
    public function getRoles();

    /**
     * Retourne les règles d'ouverture ressource(s) / groupe(s)
     *
     * @return array
     */
    public function getAllowed();

}
