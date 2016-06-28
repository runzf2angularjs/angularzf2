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

namespace Oft\Sql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Classe de migration : initialisation des données du framework
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Version20150310180000 extends AbstractMigration
{

    /**
     * Données d'initialisation
     *
     * @var array
     */
    protected $seedsValues = array(
        'oft_users' => array(
        ),
        'oft_acl_roles' => array(
            array(1, 'administrators', 'Administrateurs'),
            array(2, 'guests', 'Invités'),
        ),
        'oft_acl_role_user' => array(
        ),
        'oft_acl_resources' => array(
            array(1, 'mvc.app.index.index'),
        ),
        'oft_acl_role_resource' => array(
            array(2, 1),
        ),
    );

    /**
     * Colonnes pour les données d'initialisation
     *
     * @var array
     */
    protected $seedsKeys = array(
        'oft_users' => array(
            'id_user', 'username', 'password', 'salt', 'givenname', 'surname', 'mail', 'entity', 'manager_username', 'creation_date', 'update_time',
        ),
        'oft_acl_roles' => array(
            'id_acl_role', 'name', 'fullname',
        ),
        'oft_acl_role_user' => array(
            'id_acl_role', 'id_user',
        ),
        'oft_acl_resources' => array(
            'id_acl_resource', 'name'
        ),
        'oft_acl_role_resource' => array(
            'id_acl_role', 'id_acl_resource'
        ),
    );

    /**
     * Installation
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->setSqlInsertIntoTable('oft_users');
        $this->setSqlInsertIntoTable('oft_acl_roles');
        $this->setSqlInsertIntoTable('oft_acl_role_user');
        $this->setSqlInsertIntoTable('oft_acl_resources');
        $this->setSqlInsertIntoTable('oft_acl_role_resource');
    }

    /**
     * Désinstallation
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->setSqlInsertIntoTable('oft_acl_role_resource', 'down');
        $this->setSqlInsertIntoTable('oft_acl_resources', 'down');
        $this->setSqlInsertIntoTable('oft_acl_role_user', 'down');
        $this->setSqlInsertIntoTable('oft_acl_roles', 'down');
        $this->setSqlInsertIntoTable('oft_users', 'down');
    }

    /**
     * Insertion ou suppression des données
     *
     * @param string $table
     */
    protected function setSqlInsertIntoTable($table, $direction = 'up')
    {
        $db = $this->connection;
        foreach ($this->seedsValues[$table] as $values) {
            // QueryBuilder
            $qb = $db->createQueryBuilder();
            // Données : quotées, sauf valeurs vides : NULL
            $data = array_combine($this->seedsKeys[$table], $values);
            foreach ($data as $column => $value) {
                $data[$column] = empty($value) ? 'NULL' : $db->quote($value);
            }

            if ($direction == 'up') {
                // Insert
                $sql = $qb->insert($table)->values($data)->getSQL();
            } else { // down
                $sql = $qb->delete($table)->values($data)->getSQL();
            }

            // SQL
            $this->addSql($sql);
        }
    }

}
