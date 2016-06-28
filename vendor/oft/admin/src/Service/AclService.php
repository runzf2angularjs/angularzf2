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

namespace Oft\Admin\Service;

use Doctrine\DBAL\Connection;
use DomainException;
use Oft\Admin\Form\AclForm;
use Oft\Admin\Form\SearchForm;
use Oft\Entity\AclEntity;
use Oft\Entity\BaseEntity;
use Oft\Mvc\Application;

/**
 * Service pour la gestion des permissions d'accès
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class AclService
{

    /**
     * @var Connection
     */
    protected $db;

    /**
     * Entités
     * 
     * @var array
     */
    protected $entityClassesName = array(
        'acl' => 'Oft\Entity\AclEntity',
        'resource' => 'Oft\Entity\ResourceEntity',
        'group' => 'Oft\Entity\GroupEntity',
    );

    /**
     * Défini les champs sur lesquels la recherche est autorisée
     *
     * @var array
     */
    protected $fieldsSearch = array(
        'group' => array(
            'entity' => 'group',
            'field' => 'fullname',
            'autoComplete' => true,
        ),
        'resource' => array(
            'entity' => 'resource',
            'field' => 'name',
            'autoComplete' => true,
        ),
    );

    /**
     * Construction
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->db = $app->get('Db');
    }

    /**
     * Retourne l'instance de l'entité demandée
     *
     * @param string $name
     * @return BaseEntity
     */
    public function getEntityInstance($name)
    {
        return new $this->entityClassesName[$name]($this->db);
    }

    /**
     * Enregistre une classe d'entité
     *
     * @param string $name
     * @param string $className
     */
    public function setEntityClassName($name, $className)
    {
        $this->entityClassesName[$name] = $className;
    }

    /**
     * Retourne la configuration des champs sur lesquels la recherche est autorisée
     *
     * @return array
     */
    public function getFieldsSearch()
    {
        return $this->fieldsSearch;
    }

    /**
     * Retourne la liste préparée des règles
     *
     * @return array
     */
    public function getListing()
    {
        $dataGroups = $this->getEntityInstance('group')->fetchAllExceptAdmin();
        $dataResources = $this->getEntityInstance('resource')->fetchAll();
        $dataAcl = $this->getEntityInstance('acl')->fetchAll();

        return $this->getRulesArray($dataAcl, $dataResources, $dataGroups);
    }

    /**
     * Retourne un tableau de toutes les associations possibles de groupe/ressource
     *  - avec en première clé l'id de la ressource
     *  - avec en deuxième clé l'id de du groupe
     *  - avec en valeur un boolean indiquant si la règle existe
     *
     * @param array $dataAcl
     * @param array $dataResources
     * @param array $dataGroups
     * @return array
     */
    protected function getRulesArray($dataAcl, $dataResources, $dataGroups)
    {
        $data = array();
        $dataTempAcl = array();
        $dataTempHerit = array();

        foreach ($dataAcl as $acl) {
            $dataTempAcl[$acl['id_acl_resource']][$acl['id_acl_role']] = true;
        }

        foreach ($dataResources as $resource) {
            foreach ($dataGroups as $group) {
                $isAcl = false;
                $herit = null;
                
                if (isset($dataTempAcl[$resource['id_acl_resource']][$group['id_acl_role']])) {
                    $isAcl = true;
                    $dataTempHerit[$group['id_acl_role']][] = $resource['name'] . '.';
                } else {
                    if (isset($dataTempHerit[$group['id_acl_role']])) {
                        foreach ($dataTempHerit[$group['id_acl_role']] as $dataHerit) {
                            if (strpos($resource['name'], $dataHerit) !== false) {
                                $isAcl = true;
                                $herit = $dataHerit;
                            }
                        }
                    }
                }

                $data[$resource['id_acl_resource']][$group['id_acl_role']] = array(
                    'authorized' => $isAcl,
                    'herit' => substr($herit, 0, -1),
                );
            }
        }
        
        return $data;
    }

    /**
     * Retourne le formulaire de recherche
     *
     * @return SearchForm
     */
    public function getSearchForm($url = '')
    {
        $elements = array();
        $aclForm = new AclForm();

        foreach ($this->fieldsSearch as $field => $config) {
            if ($aclForm->has($field)) {
                $element = $aclForm->get($field);
                if ($config['autoComplete']) {
                    $element->setAttribute('data-ac-url', $url);
                    $element->setAttribute('data-ac-field', $field);
                }
                $elements[] = $element;
            }
        }

        return new SearchForm('aclSearch', $elements);
    }

    /**
     * Retourne le formulaire de création et d'édition d'une règle
     * 
     * @param int $resourceId
     * @param int $groupId
     * @return AclForm
     */
    public function getForm($resourceId = null, $groupId = null)
    {
        $form = new AclForm();
        $entity = $this->getEntityInstance('acl');

        $form->remove('group');
        $form->remove('resource');

        if ($groupId !== null || $resourceId !== null) {
            $entity->load($resourceId, $groupId);
        }

        $form->bind($entity);

        return $form;
    }

    /**
     * Sauvegarde une règle
     * 
     * @param AclEntity $aclEntity
     * @return int
     */
    public function insert(AclEntity $aclEntity)
    {
        if ($aclEntity->hasAcl()) {
            throw new DomainException('Rule already exists');
        }

        return $aclEntity->insert();
    }

    /**
     * Supprime une règle
     *
     * @param AclEntity $aclEntity
     * @param int $resourceId
     * @param int $groupId
     * @return int
     */
    public function delete(AclEntity $aclEntity, $resourceId, $groupId)
    {
        $aclEntity->load($resourceId, $groupId);
        
        return $aclEntity->delete();
    }

    /**
     * Retourne le résultat d'une recherche via l'auto-complétion
     *
     * @param int $entityKey
     * @param string $field
     * @param mixed $value
     * @return array
     * @throws DomainException
     */
    public function autoComplete($entityKey, $field, $value)
    {
        if (!isset($this->entityClassesName[$entityKey])) {
            throw new DomainException('Auto-complete refused');
        }

        $where[] = array(
            'field' => $field,
            'operator' => 'LIKE',
            'value' => $value
        );

        $entity = $this->getEntityInstance($entityKey);
        $result = $entity->fetchAll($where);
        
        $return = array();
        foreach ($result as $data) {
            $return[] = $data[$field];
        }

        return $return;
    }

}
