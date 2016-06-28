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

/**
 * Composant d'identité de connexion
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Identity
{

    /**
     * Nom d'utilisateur de l'utilisateur non-connecté
     *
     * @const string
     */
    const GUEST_USERNAME = 'guest';

    /**
     * Groupe des invités (utilisateurs 'sans droits')
     *
     * @const string
     */
    const GUEST_GROUP = 'guests';

    /**
     * Groupe des administrateurs
     *
     * @const string
     */
    const ADMIN_GROUP = 'administrators';

    /**
     * Informations sur l'identité
     *
     * @var array
     */
    protected $identity = array();

    /**
     * Informations protégées de l'identité
     *
     * @var array
     */
    protected $protectedIdentity = array(
        'username' => self::GUEST_USERNAME,
        'displayName' => '',
        'groups' => array(self::GUEST_GROUP => 'Invité'),
        'currentGroup' => self::GUEST_GROUP,
        'active' => 1,
        'language' => null
    );

    /**
     * Groupe courant
     *
     * @var string
     */
    protected $currentGroup = self::GUEST_GROUP;

    /**
     * Initialisation
     *
     * @param array $identity Informations sur l'identité
     * @return self
     */
    public function __construct(array $identity)
    {
        $this->merge($identity);
    }

    /**
     * Fusionne les données fournies à l'identité courante
     *
     * @param array $identityInfo Informations à fusionner
     * @return void
     */
    public function merge(array $identityInfo)
    {
        foreach ($identityInfo as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                unset($identityInfo[$key]);
                $this->$method($value);
            }
        }

        $this->identity = array_merge($this->identity, $identityInfo);
    }

    /**
     * Renvoie VRAI si l'utilisateur est un invité
     *
     * @return bool
     */
    public function isGuest()
    {
        return $this->getUsername() == self::GUEST_USERNAME;
    }

    /**
     * Renvoie VRAI si l'utilisateur est un administrateur
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasGroup(self::ADMIN_GROUP);
    }

    /**
     * Renvoie VRAI si le compte de l'utilisateur est actif
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->protectedIdentity['active'] ? true : false;
    }

    /**
     * Définit l'attribut "actif" du compte utilisateur
     *
     * @param bool $active
     * @return void
     */
    public function setActive($active = true)
    {
        $this->protectedIdentity['active'] = $active ? 1 : 0;
    }

    /**
     * Retourne les données de l'utilisateur sous la forme d'un tableau
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->identity, $this->protectedIdentity);
    }

    /**
     * Retourne l'identifiant de l'utilisateur
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->protectedIdentity['username'];
    }

    /**
     * Définit l'identifiant de l'utilisateur
     *
     * @param string $username Identifiant
     * @return void
     */
    public function setUsername($username)
    {
        // Le username est stocké en minuscules
        $username = strtolower($username);

        if ($username == $this->protectedIdentity['username']) {
            return;
        }

        if (!$this->isGuest()) {
            throw new \RuntimeException("Impossible de changer l'identité d'un utilisateur" . " différent de 'guest'");
        }

        $this->protectedIdentity['username'] = $username;
    }

    /**
     * Définit le nom affiché de l'utilisateur
     * 
     * @param type $displayName
     * @return void
     */
    public function setDisplayName($displayName)
    {
        $this->protectedIdentity['displayName'] = $displayName;
    }

    /**
     * Retourne le nom affichable de l'utilisateur
     * ou l'identifiant si le nom n'est pas définit
     *
     * @return string
     */
    public function getDisplayName()
    {
        if (!empty($this->protectedIdentity['displayName'])) {
            return $this->protectedIdentity['displayName'];
        }

        return ucwords($this->getUsername());
    }

    /**
     * Retourne le groupe courant de l'utilisateur
     *
     * @return string
     */
    public function getCurrentGroup()
    {
        return $this->protectedIdentity['currentGroup'];
    }

    /**
     * Définit le groupe courant de l'utilisateur
     *
     * @param string $group
     * @throws \RuntimeException
     * @return void
     */
    public function setCurrentGroup($group)
    {
        if (!$this->hasGroup($group)) {
            throw new \RuntimeException("Le groupe '$group' n'est pas défini pour cet utilisateur");
        }
        $this->protectedIdentity['currentGroup'] = $group;
    }

    /**
     * Définit le langage (préféré) de l'utilisateur
     *
     * @param string $language
     * @throws \RuntimeException
     * @return void
     */
    public function setLanguage($language)
    {
        // Doit être un code à 2 caractères
        if (!is_string($language) || strlen($language) != 2) {
            throw new \RuntimeException("La langue fournie n'est pas au bon format");
        }
        $this->protectedIdentity['language'] = strtolower($language);
    }

    /**
     * Retourne le langage (préféré) de l'utilisateur
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->protectedIdentity['language'];
    }

    /**
     * Retourne les groupes de l'utilisateur
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->protectedIdentity['groups'];
    }

    /**
     * Définit les groupes de l'utilisateur
     *
     * @param array $groups Groupes à affecter
     * @throws \RuntimeException
     * @return void
     */
    public function setGroups(array $groups)
    {
        if (!count($groups)) {
            throw new \RuntimeException("Impossible de définir une liste de groupe vide");
        }

        $this->protectedIdentity['groups'] = $groups;

        // Change le groupe courant si il n'existe pas dans la nouvelle liste
        if (!$this->hasGroup($this->getCurrentGroup())) {
            $groupsName = array_keys($groups);
            $this->setCurrentGroup($groupsName[0]);
        }

        // Chaque utilisateur fait partie du groupe "wildcard" GUEST
        if (!array_key_exists(self::GUEST_GROUP, $this->protectedIdentity['groups'])) {
            $this->protectedIdentity['groups'][self::GUEST_GROUP] = 'Invité';
        }
    }

    /**
     * Retourne VRAI si l'utilisateur est dans le groupe donné
     *
     * @param string $group Groupe
     * @return bool
     */
    public function hasGroup($group)
    {
        return array_key_exists($group, $this->protectedIdentity['groups']);
    }

    /**
     * Méthode magique SET
     *
     * Permet la modification d'un attribut de l'identité
     * Les informations protégées ne peuvent pas être modifiées par ce biais
     *
     * @param string $key Clé
     * @param mixed $value Valeur
     * @throws \RuntimeException
     * @return void
     */
    public function __set($key, $value)
    {
        if (isset($this->protectedIdentity[$key])) {
            throw new \RuntimeException("Impossible de modifier '$key'");
        }
        $this->identity[$key] = $value;
    }

    /**
     * Méthode magique GET
     *
     * Permet l'obtention de la valeur d'un attribut de l'identité
     *
     * @param string $key Clé
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->protectedIdentity[$key])) {
            return $this->protectedIdentity[$key];
        } elseif (isset($this->identity[$key])) {
            return $this->identity[$key];
        }

        return null;
    }

    /**
     * Méthode magique ISSET
     *
     * Permet de tester la présence d'un attribut de l'identité
     *
     * @param string $key Clé
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->identity[$key]) || isset($this->protectedIdentity[$key]);
    }

    /**
     * Méthode magique UNSET
     *
     * Permet de supprimer un attribut de l'identité
     * Les informations protégées ne peuvent pas être supprimées par ce biais
     *
     * @param string $key Clé
     * @throws \RuntimeException
     * @return void
     */
    public function __unset($key)
    {
        if (isset($this->protectedIdentity[$key])) {
            throw new \RuntimeException("Impossible de modifier '$key'");
        }
        unset($this->identity[$key]);
    }

}
