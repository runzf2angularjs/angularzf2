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

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Oft\Validator\Cuid;
use Oft\Validator\Password;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\Digits;
use Zend\Validator\EmailAddress;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class UserEntity extends BaseEntity implements InputFilterAwareInterface
{

    /**
     * Connexion à la base de données
     *
     * @var Connection
     */
    protected $db;

    /**
     *
     * @var array
     */
    protected $data = array(
        'id_user' => null,
        'username' => null,
        'password' => null,
        'salt' => null,
        'token' => null,
        'token_date' => null,
        'active' => null,
        'preferred_language' => null,
        'civility' => null,
        'givenname' => null,
        'surname' => null,
        'mail' => null,
        'entity' => null,
        'manager_username' => null,
        'creation_date' => null,
        'update_time' => null,
        'groups' => array()
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
     * @param int $id ID de l'utilisateur
     * @throws \DomainException
     * @return void
     */
    public function load($id)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $statement = $queryBuilder->select(
                'id_user',
                'username',
                'password',
                'salt',
                'token',
                'token_date',
                'active',
                'preferred_language',
                'civility',
                'givenname',
                'surname',
                'mail',
                'entity',
                'manager_username',
                'creation_date',
                'update_time'
            )
            ->from('oft_users', 'u')
            ->where('id_user = :id')
            ->setParameter('id', $id)
            ->execute();

        $this->data = $statement->fetch();

        if ($this->data === false) {
            throw new \DomainException('Data doesn\'t exist');
        }

        $this->data['groups'] = $this->getGroups();
    }

    /**
     * Chargement d'un élément
     *
     * @param string $username ID de l'utilisateur
     * @throws \DomainException
     */
    public function loadByUsername($username)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $statement = $queryBuilder->select(
                'id_user',
                'username',
                'password',
                'salt',
                'token',
                'token_date',
                'active',
                'preferred_language',
                'civility',
                'givenname',
                'surname',
                'mail',
                'entity',
                'manager_username',
                'creation_date',
                'update_time'
            )
            ->from('oft_users', 'u')
            ->where('username = :username')
            ->setParameter('username', $username)
            ->execute();

        $this->data = $statement->fetch();

        if ($this->data === false) {
            throw new \DomainException('User doesn\'t exists');
        }

        $this->data['groups'] = $this->getGroups();
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
                'name' => 'id_user',
                'filters' => array(
                    new StringTrim()
                ),
                'validators' => array(
                    new Digits(),
                )
            ));

            $inputFilter->add(array(
                'name' => 'username',
                'filters' => array(
                    new StripTags(),
                    new StringTrim()
                ),
                'validators' => array(
                    new NotEmpty(),
                    new Cuid(),
                )
            ));

            $inputFilter->add(array(
                'name' => 'password',
                'required' => false,
                'filters' => array(
                    new StripTags(),
                    new StringTrim()
                ),
                'validators' => array(
                    new Password('password', 'password_confirm'),
                    new NotEmpty(),
                )
            ));

            $inputFilter->add(array(
                'name' => 'password_confirm',
                'required' => false,
                'filters' => array(
                    new StripTags(),
                    new StringTrim()
                ),
                'validators' => array(
                    new Password('password', 'password_confirm'),
                    new NotEmpty(),
                )
            ));

            $inputFilter->add(array(
                'name' => 'active',
                'filters' => array(
                    new StringTrim()
                ),
                'validators' => array(
                    new NotEmpty(),
                )
            ));

            $inputFilter->add(array(
                'name' => 'preferred_language',
                'required' => false,
                'filters' => array(
                    new StripTags(),
                    new StringTrim(),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'civility',
                'required' => false,
                'filters' => array(
                    new StripTags(),
                    new StringTrim(),
                ),
                'validators' => array(
                    new NotEmpty(),
                )
            ));

            $inputFilter->add(array(
                'name' => 'givenname',
                'required' => false,
                'filters' => array(
                    new StripTags(),
                    new StringTrim(),
                ),
                'validators' => array(
                    new StringLength(array(
                        'max' => '64',
                    )),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'surname',
                'required' => false,
                'filters' => array(
                    new StripTags(),
                    new StringTrim(),
                ),
                'validators' => array(
                    new StringLength(array(
                        'max' => '64',
                    )),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'mail',
                'required' => false,
                'filters' => array(
                    new StripTags(),
                    new StringTrim(),
                ),
                'validators' => array(
                    new EmailAddress(),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'entity',
                'required' => false,
                'filters' => array(
                    new StripTags(),
                    new StringTrim(),
                ),
                'validators' => array(
                    new StringLength(array(
                        'max' => '100',
                    )),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'manager_username',
                'required' => false,
                'filters' => array(
                    new StripTags(),
                    new StringTrim(),
                ),
                'validators' => array(
                    new StringLength(array(
                        'max' => '150',
                    )),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'groups',
                'filters' => array(
                    new StripTags(),
                    new StringTrim(),
                ),
                'validators' => array(
                    new NotEmpty(),
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

        if ($this->data['password'] !== '') {
            $this->data['salt'] = $this->getSalt();
            $this->data['password'] = md5($this->data['salt'] . $this->data['password']);
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
        if ($this->data['id_user'] === null) {
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
        $result = $this->db->delete('oft_users', array(
            'id_user' => $this->data['id_user']
        ));

        if ($result === false) {
            throw new \DomainException('Impossible to delete data');
        }

        return $result;
    }

    /**
     * Teste si un nom d'utilisateur existe
     *
     * @return boolean
     */
    public function hasUser()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->select('username')
            ->from('oft_users', 'u')
            ->where('username = :username')
            ->setParameter('username', $this->data['username']);

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
        $queryBuilder->select('id_user', 'username', 'givenname', 'surname', 'mail', 'entity', 'active')
            ->from('oft_users', 'u');

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

        $queryBuilder->orderBy('id_user');

        return $queryBuilder;
    }

    /**
     * Retourne les données de tous les utilisateurs
     *
     * @param array $filters
     * @return array
     */
    public function fetchAll(array $filters = array())
    {
        $queryBuilder = $this->getQueryBuilder($filters);

        return $queryBuilder->execute();
    }

    /**
     * Retourne les groupes associés à un utilisateur
     *
     * @return array
     */
    public function getGroups()
    {
        $data = array();

        $queryBuilder = $this->db->createQueryBuilder();

        $statement = $queryBuilder->select('r.name', 'r.fullname')
            ->from('oft_acl_role_user', 'ru')
            ->innerJoin('ru', 'oft_acl_roles', 'r', 'r.id_acl_role = ru.id_acl_role')
            ->where('id_user = :id_user')
            ->setParameter('id_user', $this->data['id_user'])
            ->execute();

        foreach ($statement as $row) {
            $data[$row['name']] = $row['fullname'];
        }

        return $data;
    }

    /**
     * Ajoute un groupe
     *
     * Retourne le nombre de lignes affectées
     *
     * @param int $idAclGroup
     * @throws \DomainException
     * @return int
     */
    public function addGroup($idAclGroup)
    {
        $data = array(
            'id_acl_role' => $idAclGroup,
            'id_user' => $this->data['id_user']
        );

        $result = $this->db->insert('oft_acl_role_user', $data);

        if ($result === false) {
            throw new \DomainException('Impossible to add data');
        }

        return $result;
    }

    /**
     * Suppression d'un groupe
     *
     * @param int $idAclGroup
     * @throws \DomainException
     * @return void
     */
    public function removeGroup($idAclGroup)
    {
        $result = $this->db->delete('oft_acl_role_user', array(
            'id_user' => $this->data['id_user'],
            'id_acl_role' => $idAclGroup
        ));

        if ($result === false) {
            throw new \DomainException('Impossible to delete data');
        }
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
        $now = new DateTime();

        $dataUser = array(
            'username' => $this->data['username'],
            'password' => $this->data['password'],
            'salt' => $this->data['salt'],
            'active' => $this->data['active'],
            'preferred_language' => $this->data['preferred_language'],
            'civility' => $this->data['civility'],
            'givenname' => $this->data['givenname'],
            'surname' => $this->data['surname'],
            'mail' => $this->data['mail'],
            'entity' => $this->data['entity'],
            'manager_username' => $this->data['manager_username'],
            'creation_date' => $now->format('Y-m-d H:i:s'),
            'update_time' => $now->format('Y-m-d H:i:s'),
        );

        $result = $this->db->insert('oft_users', $dataUser);

        if ($result === false) {
            throw new \DomainException('Impossible to add data');
        }

        $this->data['id_user'] = $this->db->lastInsertId();

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
        $now = new DateTime();

        $queryBuilder->update('oft_users')
            ->set('active', ':active')
            ->set('preferred_language', ':preferred_language')
            ->set('civility', ':civility')
            ->set('givenname', ':givenname')
            ->set('surname', ':surname')
            ->set('mail', ':mail')
            ->set('entity', ':entity')
            ->set('manager_username', ':manager_username')
            ->set('update_time', ':update_time')
            ->set('active', ':active')
            ->where('id_user = :id_user');

        $this->data['update_time'] = $now->format('Y-m-d H:i:s');

        if ($this->data['password'] !== '') {
            $queryBuilder->set('password', ':password')
                ->set('salt', ':salt');
        }

        $dataUser = $this->data;
        unset($dataUser['groups']);

        $queryBuilder->setParameters($dataUser);
        $result = $queryBuilder->execute();

        if ($result === false) {
            throw new \DomainException('Impossible to modify data');
        }

        return $result;
    }

    /**
     * Retourne un "grain de sel" pour la sécurisation du mot de passe
     *
     * @return string
     */
    public function getSalt()
    {
        return dechex(mt_rand());
    }

    /**
     * Création d'un jeton de sécurité pour la réinitialisation du mot de passe
     *
     * @throws \DomainException
     * @return string
     */
    public function generateToken()
    {
        $token = md5($this->getSalt());

        $now = new DateTime();
        $now->modify('+ 2 hours');

        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->update('oft_users')
            ->set('token', ':token')
            ->set('token_date', ':token_date')
            ->where('id_user = :id_user')
            ->setParameter('id_user', $this->data['id_user'])
            ->setParameter('token', $token)
            ->setParameter('token_date', $now->format('Y-m-d H:i:s'));

        $result = $queryBuilder->execute();

        if ($result === false) {
            throw new \DomainException('Impossible to modify data');
        }

        return $token;
    }

    /**
     * Effacement du jeton pour la perte de mot de passe
     *
     * @throws \DomainException
     * @return type
     */
    public function resetToken()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->update('oft_users')
            ->set('token', ':token')
            ->set('token_date', ':token_date')
            ->where('id_user = :id_user')
            ->setParameter('id_user', $this->data['id_user'])
            ->setParameter('token', null)
            ->setParameter('token_date', null);

        $result = $queryBuilder->execute();

        if ($result === false) {
            throw new \DomainException('Impossible to modify data');
        }

        return $result;
    }

    /**
     * Retourne les données de tous les groupes
     *
     * @param array $whereOptions Options WHERE
     * @return \Doctrine\DBAL\Statement
     */
    public function getWhere(array $whereOptions = array())
    {
        $queryBuilder = $this->getQueryBuilder($whereOptions);
        return $queryBuilder->execute();
    }

    /**
     * Retourne les données de l'objet identité sous la forme d'un tableau
     *
     * @return array
     */
    public function getArrayForIdentity()
    {
        $dataUser = array(
            'username' => $this->data['username'],
            'displayName' => trim($this->data['givenname'] . ' ' . $this->data['surname']),
            'active' => $this->data['active'],
            'civility' => $this->data['civility'],
            'mail' => $this->data['mail'],
            'entity' => $this->data['entity'],
            'manager_username' => $this->data['manager_username'],
            'creation_date' => $this->data['creation_date'],
            'update_time' => $this->data['update_time'],
            'language' => $this->data['preferred_language'],
            'password' => $this->data['password'],
            'salt' => $this->data['salt'],
            'groups' => $this->data['groups']
        );

        return $dataUser;
    }

}
