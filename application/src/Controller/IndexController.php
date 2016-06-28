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

namespace App\Controller;

use Oft\Mvc\ControllerAbstract;

/**
 * Construction de l'IHM d'accueil
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class IndexController extends ControllerAbstract
{

    public function indexAction()
    {
        // Identité de l'utilisateur courant
        $identity = $this->getCurrentIdentity();

        if ($identity->isGuest()) {
            // Utilisateur invité non-authentifié
            $username = '';
        } else {
            // Utilisateur authentifié
            $username = $identity->getDisplayName();
        }

        // Envoi des variables à la vue
        return array(
            'username' => $username,
        );
    }
    
}
