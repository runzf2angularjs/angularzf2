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
use Oft\Auth\IdentityStore\IdentityStoreInterface;
use Oft\Http\Request;
use Oft\Mvc\Application;
use RuntimeException;

/**
 * Composant de lecture de l'identité pour l'authentification GASSI
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Gassi implements IdentityStoreInterface
{

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
        $this->httpRequest = $app->http->request;
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
        $identity = array();
        
        // Username
        $identity['username'] = $username;

        // Récupération des groupes
        $groups = $this->getGroups();
        if (count($groups)) {
            $identity['groups'] = $groups;
        }

        // Récupération du nom/prénom
        $displayName = $this->getDisplayName();
        if (!empty($displayName)) {
            $identity['displayName'] = $displayName;
        }

        // Récupération du mail
        $mail = $this->httpRequest->getFromServer('HTTP_FTUSERMAIL', false);
        if ($mail) {
            $identity['mail'] = $mail;
        }

        // Récupération du numéro de téléphone
        $telnb = $this->httpRequest->getFromServer('HTTP_FTUSERTELEPHONENUMBER', false);
        if ($telnb) {
            $identity['phoneNumber'] = $telnb;
        }

        // Récupération des credentials complémentaires
        $credentials = $this->httpRequest->getFromServer('HTTP_FTUSERCREDENTIALS', false);
        if ($credentials) {
            $identity['credentials'] = $credentials;
        }

        return new Identity($identity);
    }

    /**
     * Retourne les nom et prénom envoyés par le GASSI
     *
     * @return string
     */
    public function getDisplayName()
    {
        $displayName = array();
        $givenname = $this->httpRequest->getFromServer('HTTP_FTUSERGIVENNAME', false);
        if ($givenname) {
            $displayName[] = $givenname;
        }
        $sn = $this->httpRequest->getFromServer('HTTP_FTUSERSN', false);
        if ($sn) {
            $displayName[] = $sn;
        }

        return implode(' ', $displayName);
    }

    /**
     * Retourne les rôles utilisateurs éventuellement envoyés par le GASSI
     *
     * @return array
     */
    public function getGroups()
    {
        $definedGroups = $this->httpRequest->getFromServer('HTTP_FTAPPLICATIONROLES', false);
        if (!$definedGroups) {
            return array();
        }
        
        $groups = array();
        $xGroups = explode(',', $definedGroups);
        foreach ($xGroups as $group) {
            $xGroup = explode(' ', trim($group));
            $groupName = trim($xGroup[1]);
            $groups[$groupName] = $groupName;
        }

        return $groups;
    }
    
}
