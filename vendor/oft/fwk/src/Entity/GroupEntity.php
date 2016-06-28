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
use Oft\Auth\Identity;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class GroupEntity extends BaseEntity implements InputFilterAwareInterface
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
    protected $tableName = 'oft_acl_roles';

    /**
     * Définition de la table
     *
     * @var array
     */
    protected $data = array(
        'id_acl_role' => null,
        'name' => null,
        'fullname' => null
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
     * @param int $id ID du groupe
     * @throws \DomainException
     * @return void
     */
    public function load($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $statement = $queryBuilder->select(
                'id_acl_role', 'name', 'fullname')
            ->from($this->tableName, 'u')
            ->where('id_acl_role = :id')
            ->setParameter('id', $id)
            ->execute();

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
                'name' => 'fullname',
                'filters' => array(
                    new StripTags(),
                    new StringTrim()
                ),
                'validators' => array(
                    new NotEmpty(),
                    new StringLength(array(
                        'max' => '150',
                    )),
                )
            ));

            $inputFilter->add(array(
                'name' => 'name',
                'filters' => array(
                    new StripTags(),
                    new StringTrim()
                ),
                'validators' => array(
                    new NotEmpty(),
                    new StringLength(array(
                        'max' => '25',
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
     * Sauvegarde de l'élément
     *
     * @return void
     */
    public function save()
    {
        if ($this->data['id_acl_role'] === null) {
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
            'id_acl_role' => $this->data['id_acl_role']
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
            'id_acl_role' => $this->data['id_acl_role']
        ));

        if ($result === false) {
            throw new \DomainException("Impossible de supprimer la donnée");
        }

        return $result;
    }

    /**
     * Teste si un nom court existe
     *
     * @return boolean
     */
    public function hasGroup()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->select('id_acl_role')
            ->from($this->tableName, 'r')
            ->where('name = :name')
            ->setParameter('name', $this->data['name']);

        $user = $queryBuilder->execute()->fetch();

        if ($user === false) {
            return false;
        }

        return true;
    }

    /**
     * Teste si un groupe fait partie de ceux qui ne peuvent pas être supprimés
     *
     * @return boolean
     */
    public function isDisallow()
    {
        $disallowed = array(
            Identity::ADMIN_GROUP,
            Identity::GUEST_GROUP
        );

        return in_array($this->data['name'], $disallowed);
    }

    /**
     * Teste si un groupe est utilisé
     *
     * @return boolean
     */
    public function isUsed()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->select('id_acl_role')
            ->from('oft_acl_role_user', 'r')
            ->where('id_acl_role = :id_acl_role')
            ->setParameter('id_acl_role', $this->data['id_acl_role']);

        $result = $queryBuilder->execute()->fetch();

        if ($result === false) {
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
        $queryBuilder->select('id_acl_role', 'name', 'fullname')
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

        $queryBuilder->orderBy('id_acl_role');

        return $queryBuilder;
    }

    /**
     * Retourne les données de tous les groupes
     *
     * @param array $filters Options WHERE
     * @return array
     */
    public function fetchAll(array $filters = array())
    {        
        $queryBuilder = $this->getQueryBuilder($filters);

        return $queryBuilder->execute();
    }

    /**
     * Retourne les données de tous les groupes
     * 
     * @param array $filters
     * @return array
     */
    public function fetchAllExceptAdmin(array $filters = array())
    {
        $result = array();
        
        $data = $this->fetchAll($filters);

        foreach ($data as $group) {
            if ($group['name'] != 'administrators') {
                $result[] = $group;
            }
        }

        return $result;
    }

    /**
     * Retourne les données sous la forme d'un tableau associatif clé/valeur
     *
     * @param string $idColumn Nom de la colonne clé primaire souhaité en "clé"
     * @param string $nameColumn Nom de la colonne souhaité en "valeur"
     * @param string $orderCol Règle de tri du résultat
     * @throws \RuntimeException
     * @return array
     */
    public function getSelectValues($idColumn = null, $nameColumn = null, $orderCol = null)
    {
        if (is_null($idColumn) || is_null($nameColumn)) {
            throw new \RuntimeException('Paramètre invalide');
        }

        if (is_null($orderCol)) {
            $orderCol = $nameColumn;
        }

        $result = array();
        $queryBuilder = $this->db->createQueryBuilder();

        $statement = $queryBuilder->select($idColumn, $nameColumn)
            ->from($this->tableName, 'r')
            ->orderBy($orderCol)
            ->execute();
        
        foreach ($statement as $row) {
            $result[$row[$idColumn]] = $row[$nameColumn];
        }

        return $result;
    }

    /**
     * Retourne les données d'un groupe par son nom court
     *
     * @param string $name Nom court du groupe
     * @throws \DomainException
     * @return array
     */
    public function getByName($name)
    {
        $option[] = array(
            'field' => 'name',
            'value' => $name,
        );

        $queryBuilder = $this->getQueryBuilder($option);
        $statement = $queryBuilder->execute();
        $data = $statement->fetch();

        if ($data === false) {
            throw new \DomainException('Data doesn\'t exist');
        }

        return $data;
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
        $dataGroup = array(
            'name' => $this->data['name'],
            'fullname' => $this->data['fullname']
        );

        $result = $this->db->insert($this->tableName, $dataGroup);

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
            ->set('fullname', ':fullname')
            ->where('id_acl_role = :id_acl_role');

        $queryBuilder->setParameters($this->data);
        $result = $queryBuilder->execute();

        if ($result === false) {
            throw new \DomainException('Impossible to modify data');
        }

        return $result;
    }

}
