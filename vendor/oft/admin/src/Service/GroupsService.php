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

use Doctrine\DBAL\Portability\Connection;
use DomainException;
use Oft\Admin\Form\GroupForm;
use Oft\Admin\Form\SearchForm;
use Oft\Entity\BaseEntity;
use Oft\Entity\GroupEntity;
use Oft\Mvc\Application;
use Oft\Paginator\Adapter\QueryBuilder as QueryBuilderAdapter;
use Zend\Paginator\Paginator;

/**
 * Service pour la gestion des groupes
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class GroupsService
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
        'group' => '\Oft\Entity\GroupEntity'
    );

    /**
     * Défini les champs sur lesquels la recherche est autorisée
     *
     * @var array
     */
    protected $fieldsSearch = array(
        'name' => array(
            'entity' => 'group',
            'field' => 'name',
            'autoComplete' => true,
        ),
        'fullname' => array(
            'entity' => 'group',
            'field' => 'fullname',
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
     * Retourne la liste des groupes paginée
     *
     * @param array $data
     * @return Paginator
     */
    public function getPaginator(array $data = array())
    {
        $whereOptions = array();
        $entity = $this->getEntityInstance('group');

        foreach ($this->fieldsSearch as $field => $config) {
            if (isset($data[$field]) && $data[$field] != '') {
                $whereOptions[] = array(
                    'field' => $field,
                    'operator' => 'LIKE',
                    'value' => $data[$field],
                );
            }
        }

        $queryBuilder = $entity->getQueryBuilder($whereOptions);
        $adapter = new QueryBuilderAdapter(
            $queryBuilder,
            $this->entityClassesName['group']
        );

        return new Paginator($adapter);
    }

    /**
     * Retourne le formulaire de recherche
     *
     * @return SearchForm
     */
    public function getSearchForm($url = '')
    {
        $elements = array();
        $groupForm = new GroupForm();

        foreach ($this->fieldsSearch as $field => $config) {
            if ($groupForm->has($field)) {
                $element = $groupForm->get($field);
                if ($config['autoComplete']) {
                    $element->setAttribute('data-ac-url', $url);
                    $element->setAttribute('data-ac-field', $field);
                }
                $elements[] = $element;
            }
        }

        return new SearchForm('group', $elements);
    }

    /**
     * Retourne le formulaire de création et d'édition d'un groupe
     *
     * @param int $groupId
     * @return GroupForm
     */
    public function getForm($groupId = null)
    {
        $form = new GroupForm();
        $entity = $this->getEntityInstance('group');

        if ($groupId === null) {
            $entity->getInputFilter()->remove('id_acl_role');
            $form->remove('id_acl_role');
        } else {
            $entity->load($groupId);
            $entity->getInputFilter()->remove('name');
            $form->get('name')->setAttributes(array('disabled' => 'disabled'));
        }

        $form->bind($entity);

        return $form;
    }

    /**
     * Retourne le formulaire de création et d'édition d'un groupe en lecture seule
     *
     * @param int $groupId
     * @return GroupForm
     */
    public function getFormReadOnly($groupId)
    {
        $form = $this->getForm($groupId);
                        
        $form->remove('submit');
        $form->remove('reset');
        
        $elements = $form->getElements();
        foreach ($elements as $element) {
            $element->setAttributes(array('disabled' => 'disabled'));
        }

        return $form;
    }

    /**
     * Sauvegarde un groupe
     *
     * @param GroupEntity $group
     * @throws DomainException
     */
    public function insert(GroupEntity $group)
    {
        if ($group->hasGroup()) {
            throw new DomainException('Group already exists');
        }

        $group->save();
    }

    /**
     * Met à jour les données d'un groupe
     *
     * @param GroupEntity $group
     */
    public function update(GroupEntity $group)
    {
        $group->save();
    }

    /**
     * Supprime un groupe
     *
     * @param GroupEntity $group
     * @param int $groupId
     */
    public function delete(GroupEntity $group, $groupId)
    {
        $group->load($groupId);

        if ($group->isDisallow()) {
            throw new DomainException('This can\'t be deleted');
        }

        if ($group->isUsed()) {
            throw new DomainException(
            'This can\'t be deleted' . ' ' .
            'because at least one user is associated'
            );
        }

        $group->deleteGroupResources();
        $group->delete();
    }

    /**
     * Retourne tous les groupes
     *
     * @param array $filters
     * @return array
     */
    public function fetchAll(array $filters = array())
    {
        return $this->getEntityInstance('group')
            ->fetchAll($filters);
    }

    /**
     * Retourne tous les groupes sauf l'administrateur
     *
     * @param array $filters
     * @return array
     */
    public function fetchAllExceptAdmin(array $filters = array())
    {
        return $this->getEntityInstance('group')
            ->fetchAllExceptAdmin($filters);
    }

    /**
     * Retourne un groupe par son ID
     * 
     * @param int $groupId
     * @return array
     */
    public function getById($groupId)
    {
        $group = $this->getEntityInstance('group');
        $group->load($groupId);

        return $group->getArrayCopy();
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
