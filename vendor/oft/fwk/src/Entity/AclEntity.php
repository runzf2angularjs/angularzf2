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

use Doctrine\DBAL\Portability\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Zend\Filter\StringTrim;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\Digits;

class AclEntity extends BaseEntity implements InputFilterAwareInterface
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
    protected $tableName = 'oft_acl_role_resource';

    /**
     * Définition de la table
     *
     * @var array
     */
    protected $data = array(
        'id_acl_role' => null,
        'id_acl_resource' => null,
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
     * @param int $resourceId ID de la ressource ACL
     * @param int $groupId ID du groupe utilisateurs
     * @throws \DomainException
     * @return void
     */
    public function load($resourceId, $groupId)
    {
        $option = array(
            array(
                'field' => 'id_acl_resource',
                'value' => $resourceId,
            ),
            array(
                'field' => 'id_acl_role',
                'value' => $groupId,
            ),
        );

        $queryBuilder = $this->getQueryBuilder($option);

        $statement = $queryBuilder->execute();

        $this->data = $statement->fetch();

        if ($this->data === false) {
            throw new \DomainException('Data doesn\'t exist');
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
                'name' => 'id_acl_role',
                'filters' => array(
                    new StringTrim()
                ),
                'validators' => array(
                    new Digits(),
                )
            ));

            $inputFilter->add(array(
                'name' => 'id_acl_resource',
                'filters' => array(
                    new StringTrim()
                ),
                'validators' => array(
                    new Digits(),
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
     * Insertion d'un élément
     *
     * Retourne le nombre de lignes affectées
     *
     * @throws \DomainException
     * @return int
     */
    public function insert()
    {
        $data = array(
            'id_acl_role' => $this->data['id_acl_role'],
            'id_acl_resource' => $this->data['id_acl_resource'],
        );

        $result = $this->db->insert($this->tableName, $data);

        if ($result === false) {
            throw new \DomainException('Impossible to add data');
        }

        return $result;
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
            'id_acl_role' => $this->data['id_acl_role'],
            'id_acl_resource' => $this->data['id_acl_resource'],
        ));

        if ($result === false) {
            throw new \DomainException('Impossible to delete data');
        }

        return $result;
    }

    /**
     * Retourne les données de toutes les règles
     * 
     * @param array $filters
     * @return \Doctrine\DBAL\Statement
     */
    public function fetchAll(array $filters = array())
    {        
        $queryBuilder = $this->getQueryBuilder($filters);

        return $queryBuilder->execute();
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
        $queryBuilder->select('id_acl_resource', 'id_acl_role')
            ->from($this->tableName, 'rr');

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

        return $queryBuilder;
    }

    /**
     * Test si un élément existe déjà
     *
     * @return boolean
     */
    public function hasAcl()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->select('id_acl_resource')
            ->from($this->tableName, 'r')
            ->where('id_acl_resource = :id_acl_resource')
            ->andWhere('id_acl_role = :id_acl_role')
            ->setParameter('id_acl_resource', $this->data['id_acl_resource'])
            ->setParameter('id_acl_role', $this->data['id_acl_role']);

        $user = $queryBuilder->execute()->fetch();

        if ($user === false) {
            return false;
        }

        return true;
    }

}
