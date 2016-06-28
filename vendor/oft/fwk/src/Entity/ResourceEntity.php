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

namespace Oft\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\Digits;
use Zend\Validator\Regex;

class ResourceEntity extends BaseEntity implements InputFilterAwareInterface
{

    /**
     * Connexion à la base de données
     *
     * @var Connection
     */
    protected $db;

    /**
     * Nom de la table
     *
     * @var string
     */
    protected $tableName = 'oft_acl_resources';

    /**
     * Définition de la table
     *
     * @var array
     */
    protected $data = array(
        'id_acl_resource' => null,
        'name' => null,
        'type' => null,
        'module' => null,
        'controller' => null,
        'action' => null,
    );

    /**
     * Composant de filtrage
     *
     * @var InputFilterInterface
     */
    protected $inputFilter;

    /**
     * Initialisation
     *
     * @param Connection $db
     * @return self
     */
    public function __construct($db = null)
    {
        $this->db = $db;
    }

    /**
     * Chargement d'un élément
     *
     * @param int $id ID de la ressource ACL
     * @throws \DomainException
     * @return void
     */
    public function load($id)
    {
        $option[] = array(
            'field' => 'id_acl_resource',
            'value' => $id,
        );

        $queryBuilder = $this->getQueryBuilder($option);

        $statement = $queryBuilder->execute();

        $data = $statement->fetch();

        if ($data === false) {
            throw new \DomainException('Data doesn\'t exist');
        }

        $this->setAclData($data['name']);

        $this->data['id_acl_resource'] = $data['id_acl_resource'];
    }

    /**
     * Définit les données de la ressource à partir d'une ressource MVC
     * au format "mvc.module.controller.action"
     *
     * @param string $name Ressource MVC
     * @throws \RuntimeException
     * @return void
     */
    public function setAclData($name)
    {
        $this->data['name'] = $name;

        $parts = explode('.', $name);

        $this->data['type'] = array_shift($parts);

        switch ($this->data['type']) {
            case 'mvc':
                $module = array_shift($parts);
                if ($module !== null) {
                    $this->data['module'] = $module;
                }


                $controller = array_shift($parts);
                if ($controller !== null) {
                    $this->data['controller'] = null;
                }
                $this->data['controller'] = $controller;


                $action = array_shift($parts);
                if ($action !== null) {
                    $this->data['action'] = null;
                }
                $this->data['action'] = $action;

                break;
            default:
                throw new \RuntimeException('Only \'MVC\' type accepted');
        }
    }

    /**
     * Définit le composant de filtrage
     *
     * @param InputFilterInterface $inputFilter
     * @return void
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    /**
     * Définit les règles de filtrage et validation puis
     * retourne le composant de filtrage initialisé
     *
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        if ($this->inputFilter === null) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name' => 'id_acl_resource',
                'filters' => array(
                    new StringTrim()
                ),
                'validators' => array(
                    new Digits(),
                )
            ));

            $inputFilter->add(array(
                'name' => 'type',
                'filters' => array(
                    new StripTags(),
                    new StringTrim()
                ),
            ));

            $inputFilter->add(array(
                'name' => 'module',
                'filters' => array(
                    new StripTags(),
                    new StringTrim()
                ),
                'validators' => array(
                    new Regex(array(
                        'pattern' => '/^[a-z]{1}[a-z-]*$/',
                        'message' => 'Only lowercase letters and underscores accepted'
                        )),
                )
            ));

            $inputFilter->add(array(
                'name' => 'controller',
                'required' => false,
                'filters' => array(
                    new StripTags(),
                    new StringTrim()
                ),
                'validators' => array(
                    new Regex(array(
                        'pattern' => '/^[a-z]{1}[0-9a-z -]*$/',
                        'message' => 'Only lowercase letters, numbers and dashes accepted'
                        )),
                )
            ));

            $inputFilter->add(array(
                'name' => 'action',
                'required' => false,
                'filters' => array(
                    new StripTags(),
                    new StringTrim()
                ),
                'validators' => array(
                    new Regex(array(
                        'pattern' => '/^[a-z]{1}[0-9a-z -]*$/',
                        'message' => 'Only lowercase letters, numbers and dashes accepted'
                        )),
                )
            ));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * Alimente l'attribut "data" à partir d'un tableau de données
     *
     * @param array $data Tableau de données
     * @return void
     */
    public function exchangeArray($data)
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                $this->data[$key] = $value;
            }
        }

        switch ($this->data['type']) {
            case 'mvc':
                $parts = array($this->data['type'], $this->data['module']);
                if (isset($this->data['controller']) && !empty($this->data['controller'])) {
                    $parts[] = $this->data['controller'];
                    if (isset($this->data['action']) && !empty($this->data['action'])) {
                        $parts[] = $this->data['action'];
                    }
                }
                $this->data['name'] = implode('.', $parts);
                break;
            default:
                throw new \RuntimeException('Only \'MVC\' type accepted');
        }
    }

    /**
     * Retourne les données de l'objet sous la forme d'un tableau
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->data;
    }

    /**
     * Enregistrement d'une ressource
     *
     * @return void
     */
    public function save()
    {
        if ($this->data['id_acl_resource'] === null) {
            $this->insert();
        } else {
            $this->update();
        }
    }

    /**
     * Suppression d'un élément
     *
     * Retourne le nombre de lignes affectées
     *
     * @throws \DomainException
     * @return int
     */
    public function delete()
    {
        $result = $this->db->delete($this->tableName, array(
            'id_acl_resource' => $this->data['id_acl_resource']
        ));

        if ($result === false) {
            throw new \DomainException('Impossible to delete data');
        }

        return $result;
    }
    
    /**
     * Suppression des associations aux ressources
     * 
     * @return type
     * @throws \DomainException
     */
    public function deleteGroupResources()
    {
        $result = $this->db->delete('oft_acl_role_resource', array(
            'id_acl_resource' => $this->data['id_acl_resource']
        ));

        if ($result === false) {
            throw new \DomainException("Impossible de supprimer la donnée");
        }

        return $result;
    }

    /**
     * Teste si la ressource existe
     *
     * @return boolean
     */
    public function hasResource()
    {
        $option[] = array(
            'field' => 'name',
            'value' => $this->data['name'],
        );

        $queryBuilder = $this->getQueryBuilder($option);

        $user = $queryBuilder->execute()->fetch();

        if ($user === false) {
            return false;
        }

        return true;
    }

    /**
     * Retourne l'objet de construction de requêtes initialisé
     *
     * @param array $whereOptions Options WHERE
     * @return QueryBuilder
     */
    public function getQueryBuilder(array $whereOptions = array())
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('id_acl_resource', 'name')
            ->from($this->tableName, 'r');

        $where = false;
        foreach ($whereOptions as $data) {
            if (!isset($data['operator'])) {
                $data['operator'] = '=';
            }

            if ($data['operator'] === 'LIKE') {
                $data['value'] = '%' . $data['value'] . '%';
            }

            $sqlWhere = $data['field'].' '.$data['operator'].' :'.$data['field'];

            if ($where) {
                $queryBuilder->andWhere($sqlWhere);
            } else {
                $queryBuilder->where($sqlWhere);
                $where = true;
            }

            $queryBuilder->setParameter($data['field'], $data['value']);
        }

        $queryBuilder->orderBy('name');

        return $queryBuilder;
    }

    /**
     * Retourne les données de toutes les ressources
     *
     * @param array $filters Options WHERE
     * @return \Doctrine\DBAL\Statement
     */
    public function fetchAll(array $filters = array())
    {        
        $queryBuilder = $this->getQueryBuilder($filters);

        return $queryBuilder->execute();
    }

    /**
     * Insertion d'un élément
     *
     * Retourne le nombre de lignes affectées
     *
     * @throws \DomainException
     * @return int
     */
    protected function insert()
    {
        $data = array(
            'name' => $this->data['name'],
        );

        $result = $this->db->insert($this->tableName, $data);

        if ($result === false) {
            throw new \DomainException('Impossible to add data');
        }

        return $result;
    }

    /**
     * Mise à jour d'un élément
     *
     * Retourne le nombre de lignes affectées
     *
     * @throws \DomainException
     * @return int
     */
    protected function update()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->update($this->tableName)
            ->set('name', ':name')
            ->where('id_acl_resource = :id_acl_resource');

        $queryBuilder->setParameters($this->data);
        $result = $queryBuilder->execute();

        if ($result === false) {
            throw new \DomainException('Impossible to modify data');
        }

        return $result;
    }

}
