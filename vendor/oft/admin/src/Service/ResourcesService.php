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
use Oft\Admin\Form\ResourceForm;
use Oft\Admin\Form\SearchForm;
use Oft\Entity\BaseEntity;
use Oft\Entity\ResourceEntity;
use Oft\Mvc\Application;
use Oft\Paginator\Adapter\QueryBuilder as QueryBuilderAdapter;
use Zend\Paginator\Paginator;

/**
 * Service pour la gestion des ressources
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class ResourcesService
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
        'resource' => '\Oft\Entity\ResourceEntity'
    );

    /**
     * Défini les champs sur lesquels la recherche est autorisée
     *
     * @var array
     */
    protected $fieldsSearch = array(
        'name' => array(
            'entity' => 'resource',
            'field' => 'name',
            'autoComplete' => true,
        ),
    );

    /**
     * Modules
     * 
     * @var array
     */
    protected $modules;

    /**
     * Construction
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->modules = array_keys($app->get('ModuleManager')->getModules());
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
     * Retourne la liste des ressources paginée
     *
     * @param array $data
     * @return Paginator
     */
    public function getPaginator(array $data = array())
    {
        $whereOptions = array();
        $entity = $this->getEntityInstance('resource');

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
            $this->entityClassesName['resource']
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
        $resourceForm = new ResourceForm();

        foreach ($this->fieldsSearch as $field => $config) {
            if ($resourceForm->has($field)) {
                $element = $resourceForm->get($field);
                if ($config['autoComplete']) {
                    $element->setAttribute('data-ac-url', $url);
                    $element->setAttribute('data-ac-field', $field);
                }
                $elements[] = $element;
            }
        }

        return new SearchForm('resource', $elements);
    }

    /**
     * Retourne le formulaire de création et d'édition d'une ressource
     *
     * @param int $resourceId
     * @return ResourceForm
     */
    public function getForm($resourceId = null)
    {
        $form = new ResourceForm();
        $entity = $this->getEntityInstance('resource');

        $modules = array();
        foreach ($this->modules as $moduleName) {
            $modules[$moduleName] = $moduleName;
        }

        $form->get('module')->setValueOptions($modules);
        $form->remove('name');

        if ($resourceId === null) {
            $entity->getInputFilter()->remove('id_acl_resource');
            $form->remove('id_acl_resource');
        } else {
            $entity->load($resourceId);
        }

        $form->bind($entity);

        return $form;
    }

    /**
     * Retourne le formulaire de création et d'édition d'une ressource en lecture seule
     *
     * @param int $resourceId
     * @return ResourceForm
     */
    public function getFormReadOnly($resourceId)
    {
        $form = $this->getForm($resourceId);
        
        $form->remove('submit');
        $form->remove('reset');
        
        $elements = $form->getElements();
        foreach ($elements as $element) {
            $element->setAttributes(array('disabled' => 'disabled'));
        }

        return $form;
    }

    /**
     * Sauvegarde d'une ressource
     *
     * @param ResourceEntity $resource
     * @throws DomainException
     */
    public function insert(ResourceEntity $resource)
    {
        if ($resource->hasResource()) {
            throw new DomainException('Resource already exists');
        }

        $resource->save();
    }

    /**
     * Met à jour les données d'une ressource
     *
     * @param ResourceEntity $resource
     */
    public function update(ResourceEntity $resource)
    {
        $resource->save();
    }

    /**
     * Supprime une ressource
     *
     * @param ResourceEntity $resource
     * @param string $resourceId
     */
    public function delete(ResourceEntity $resource, $resourceId)
    {
        $resource->load($resourceId);
        
        $resource->deleteGroupResources();
        $resource->delete();
    }

    /**
     * Retourne toutes les ressources
     *
     * @param array $filters
     * @return array
     */
    public function fetchAll(array $filters = array())
    {
        return $this->getEntityInstance('resource')
            ->fetchAll($filters);
    }

    /**
     * Retourne une ressource par son ID
     *
     * @param int $resourceId
     * @return array
     */
    public function getById($resourceId)
    {
        $entity = $this->getEntityInstance('resource');
        $entity->load($resourceId);

        return $entity->getArrayCopy();
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
