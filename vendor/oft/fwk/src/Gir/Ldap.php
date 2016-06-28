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

use InvalidArgumentException;
use Oft\Gir\Ldap\Connection;
use Oft\Mvc\Application;
use Oft\Validator\Cuid;
use RuntimeException;

/**
 * Composant d'interrogation de l'annuaire interne via le protocole LDAP
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Ldap extends Connection implements GirInterface
{

    /**
     * Attributs retournés par défaut (méthodes 'search')
     * @var array
     */
    protected $defaultAttributes = array(
        'uid', 'sn', 'givenname',
        'telephonenumber', 'mobile', 'othertelephone',
        'mail', 'civility', 'preferredlanguage',
        'postaladdress', 'ftadmou',
        'manager'
    );

    /**
     * Catégorie de collaborateurs à récupérer du LDAP
     * @var array
     */
    protected static $onlyGirCategory = array(
        'CI', // Collaborateur Interne
        'CM', // Collaborateur Interne extra Muros
        'CE', // Collaborateur Externe
        'EX', // Partenaire externe (External player)
        'GC_II' // Compte Intérimaire
    );

    /**
     * Attributs ignorés lors de la normalisation
     * @var array
     */
    protected $ignoredAttributes = array(
        'sn;normalize', 'givenname;normalize'
    );

    /**
     * Initialisation
     *
     * @param Application
     * @throws RuntimeException
     */
    public function __construct(Application $app)
    {
        $config = $app->config['gir'];

        if ($config['active'] !== true) {
            throw new RuntimeException('GIR functionality is not enabled');
        }

        if (!extension_loaded('ldap')) {
            throw new RuntimeException('LDAP extension is not loaded');
        }
        
        $this->setOptions($config['ldap']);
        $this->bind();
    }

    /**
     * Chercher un agent par code Alliance, Nom ou E-Mail
     * Retourne false si aucun résultat
     *
     * @param string $term Minimum 4 caractères
     * @param array $attributes Liste des attributs à retourner
     * @return array|bool
     */
    public function findCollaboratorsByUidOrCnOrMail($term, array $attributes = null)
    {
        if ($attributes === null) {
            $attributes = $this->defaultAttributes;
        }

        if (!is_string($term) || strlen($term) < 4) {
            return false;
        }

        $filter = "(|(uid=*$term*)(cn=*$term*)(mail=*$term*))";

        return $this->findCollaborators($filter, $attributes);
    }

    /**
     * Recherche un profil utilisateur
     *
     * @param mixed $searchTerms Critères de recherche :
     *     - string sous la forme (cle=valeur)
     *     - array('attribut' => 'valeur')
     * @param array $attributes
     * @param bool $normalize
     * @return array
     */
    public function findCollaborators($searchTerms, array $attributes = null, $normalize = true)
    {
        if ($attributes === null) {
            $attributes = $this->defaultAttributes;
        }

        $filter = null;
        if (is_array($searchTerms)) {
            $filterContain = '';
            foreach ($searchTerms as $k => $v) {
                if (!is_string($k)) {
                    throw new InvalidArgumentException("Numerical parameter not expected");
                }

                $filterContain .= "($k=*$v*)";
            }

            $filter = "(&$filterContain)";
        } elseif (is_string($searchTerms)) {
            $filter = $searchTerms;
        } else {
            throw new InvalidArgumentException("Search terms parameter is not valid");
        }

        $search = ldap_search($this->getResource(), $this->options['baseDn'], $filter, $attributes);

        $entries = ldap_get_entries($this->getResource(), $search);
        unset($entries['count']);

        $data = array();
        foreach ($entries as $entry) {
            if ($normalize) {
                $data[] = $this->normalizeCollaborator($entry, $attributes);
            } else {
                $data[] = $entry;
            }
        }

        return $data;
    }

    /**
     * Normalise un résultat retourné par l'annuaire
     *
     * @param $collaboratorData
     * @param array|null $attributes
     * @return array
     */
    public function normalizeCollaborator($collaboratorData, array $attributes = null)
    {
        if ($attributes === null) {
            $attributes = $this->defaultAttributes;
        }

        // Normalize collaborator
        $newCollaboratorData = array();
        foreach ($collaboratorData as $key => $data) {
            if (in_array($key, $this->ignoredAttributes)) {
                continue;
            }

            if (in_array($key, $attributes)) {
                if (is_array($data) && $data['count'] == 1) {
                    $newCollaboratorData[$key] = $data[0];
                } elseif (is_array($data)) {
                    $newCollaboratorData[$key] = $data;
                    unset($newCollaboratorData[$key]['count']);
                } else {
                    $newCollaboratorData[$key] = $data;
                }
            }
        }

        if (array_key_exists('postaladdress', $newCollaboratorData)) {
            $newCollaboratorData['postaladdress'] = trim(
                    preg_replace(
                            '/\$+/', "\n", $newCollaboratorData['postaladdress']
                    )
            );
        }

        if (array_key_exists('ftadmou', $newCollaboratorData)) {
            $ftAdmou = '';
            $ftAdmouParts = preg_split('/,/', $newCollaboratorData['ftadmou']);
            foreach ($ftAdmouParts as $ftAdmouPart) {
                if ($ftAdmouPart == 'ou=entities') {
                    break;
                }
                $ftAdmou = str_replace('ou=', '', $ftAdmouPart) . (strlen($ftAdmou) > 1 ? '/' . $ftAdmou : '');
            }
            $newCollaboratorData['ftadmou'] = $ftAdmou;
        }

        return $newCollaboratorData;
    }

    /**
     * Récupère les informations depuis le cache ou directement si le cache est vide
     *
     * @param string $uid Identifiant du collaborateur
     * @param array $attributes Liste des attributs à retourner
     * @return array
     */
    public function getCollaborator($uid, array $attributes = null)
    {
        $uidValidator = new Cuid();
        if (!$uidValidator->isValid($uid)) {
            return array();
        }

        $filter = array('uid' => $uid);

        $collaborator = $this->findCollaborators($filter, $attributes);
        if (count($collaborator) != 1) {
            return array();
        }

        return $collaborator[0];
    }

    /**
     * Récupère la photo d'un utilisateur
     *
     * @param string $uid Identifiant du collaborateur
     * @return array
     */
    public function getCollaboratorPhoto($uid)
    {
        $filter = array('uid' => $uid);

        $collaborators = $this->findCollaborators($filter, array('jpegphoto'));
        if (count($collaborators) != 1) {
            return null;
        }
        $collaborator = $collaborators[0];

        if (!array_key_exists('jpegphoto', $collaborator)) {
            return null;
        }

        return $collaborator['jpegphoto'];
    }

    /**
     * Vérifie si un collaborateur est un manager d'une équipe
     *
     * @return boolean
     */
    public function getIsManager($uid)
    {
        $filter = array('uid' => $uid);

        $data = $this->findCollaborators($filter, array('dn'));

        if (count($data) != 1) {
            return false;
        }

        $managerDn = $data[0];
        
        $filterManagerDn = "(manager=" . $managerDn['dn'] . ")";
       
        $result = $this->findCollaborators($filterManagerDn, array('ftentityid'));

        return (! empty($result));
    }

    /**
     * Récupère la liste des collaborateurs d'un collaborateur
     *
     * @param string $uid
     * @param array $attributes
     * @return bool|array
     */
    public function getCollaboratorTeam($uid, array $attributes = null)
    {
        if ($attributes === null) {
            $attributes = $this->defaultAttributes;
        }

        $filter = "(uid=$uid)";

        $data = $this->findCollaborators($filter, array('ftadmou'), false);

        if (count($data) != 1) {
            return null;
        }
        $collaborator = $data[0];

        // Filter Dn
        $dnFilter = "(ftadmou=" . $collaborator['ftadmou'][0] . ")";
        // Filter GIR
        $filterGirCategories = '';
        foreach (self::$onlyGirCategory as $girCategory) {
            $filterGirCategories .= "(gircategory=" . $girCategory . ")";
        }
        $filterGirCategory = "(|$filterGirCategories)";
        // Filter global
        $filter = "(&$dnFilter$filterGirCategory)";

        $search = $this->findCollaborators($filter, $attributes);

        return $search;
    }

    /**
     * Récupère le code alliance (identifiant salarié != bureautique)
     *
     * @param  string $uid
     * @return mixed
     */
    public function getLeid($uid)
    {
        $filter = "(uid=$uid)";

        $data = $this->findCollaborators($filter, array('employeenumber'));

        if (count($data) != 1 || !isset($data[0]['employeenumber'])) {
            return false;
        }
        
        return $data[0]['employeenumber'];
    }

}
