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

namespace Oft\Gir;

/**
 * Interface des composants d'interrogation de l'annuaire interne
 *
 * @author CC PHP <cdc.php@orange.com>
 */
interface GirInterface
{
    public function findCollaborators($searchTerms, array $attributes = null, $normalize = true);
    public function findCollaboratorsByUidOrCnOrMail($term, array $attributes = null);
    public function getCollaborator($uid, array $attributes = null);
    public function getCollaboratorPhoto($uid);
    public function getCollaboratorTeam($uid, array $attributes = null);
    public function getIsManager($uid);
    public function getLeid($uid);
}
