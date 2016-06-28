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
 * Classe de migration : initialisation des tables du framework
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Version20150310170000 extends AbstractMigration
{

    /**
     * Installation
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->createTableOftUsers($schema);
        $this->createTableOftAclRoles($schema);
        $this->createTableOftAclRoleUser($schema);
        $this->createTableOftAclResources($schema);
        $this->createTableOftAclRoleResource($schema);
    }

    /**
     * Désinstallation
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('oft_users');
        $schema->dropTable('oft_acl_roles');
        $schema->dropTable('oft_acl_role_user');
        $schema->dropTable('oft_acl_resources');
        $schema->dropTable('oft_acl_role_resource');
    }

    /**
     * Création de la table oft_users
     *
     * @param Schema $schema
     */
    protected function createTableOftUsers(Schema $schema)
    {
        $table = $schema->createTable('oft_users');

        $table->addOption('collate', 'utf8_general_ci');

        // id_user
        $table->addColumn('id_user', 'integer')
            ->setLength(10)
            ->setUnsigned(true)
            ->setAutoincrement(true);
        // username
        $table->addColumn('username', 'string')
            ->setLength(150);
        // password
        $table->addColumn('password', 'string')
            ->setLength(32)
            ->setFixed(true);
        // salt
        $table->addColumn('salt', 'string')
            ->setLength(8)
            ->setFixed(true);
        // token
        $table->addColumn('token', 'string')
            ->setLength(32)
            ->setFixed(true)
            ->setNotnull(false);
        // token_date
        $table->addColumn('token_date', 'datetime')
            ->setNotnull(false);
        // active
        $table->addColumn('active', 'boolean')
            ->setDefault(1);
        // preferred_language
        $table->addColumn('preferred_language', 'string')
            ->setLength(2)
            ->setFixed(true)
            ->setDefault('FR');
        // civility
        $table->addColumn('civility', 'boolean')
            ->setDefault(0);
        // givenname
        $table->addColumn('givenname', 'string')
            ->setLength(64)
            ->setNotnull(false);
        // surname
        $table->addColumn('surname', 'string')
            ->setLength(64)
            ->setNotnull(false);        
        // mail
        $table->addColumn('mail', 'string')
            ->setLength(100)
            ->setNotnull(false);        
        // entity
        $table->addColumn('entity', 'string')
            ->setLength(100)
            ->setNotnull(false);        
        // manager_username
        $table->addColumn('manager_username', 'string')
            ->setLength(150)
            ->setNotnull(false);        
        // creation_date
        $table->addColumn('creation_date', 'datetime')
            ->setNotnull(false);
        // update_time
        $table->addColumn('update_time', 'datetime')
            ->setNotnull(false);
        
        $table
            ->setPrimaryKey(array('id_user'))
            ->addUniqueIndex(array('username'), 'ux_users_username');
    }

    /**
     * Création de la table oft_acl_roles
     *
     * @param Schema $schema
     */
    protected function createTableOftAclRoles(Schema $schema)
    {
        $table = $schema->createTable('oft_acl_roles');

        $table->addOption('collate', 'utf8_general_ci');

        // id_acl_role
        $table->addColumn('id_acl_role', 'integer')
            ->setLength(10)
            ->setUnsigned(true)
            ->setAutoincrement(true);
        // name
        $table->addColumn('name', 'string')
            ->setLength(25);
        // fullname
        $table->addColumn('fullname', 'string')
            ->setLength(150);

        $table
            ->setPrimaryKey(array('id_acl_role'))
            ->addUniqueIndex(array('name'), 'ux_acl_role_name');
    }

    /**
     * Création de la table oft_acl_role_user
     *
     * @param Schema $schema
     */
    protected function createTableOftAclRoleUser(Schema $schema)
    {
        $table = $schema->createTable('oft_acl_role_user');

        $table->addOption('collate', 'utf8_general_ci');

        // id_acl_role
        $table->addColumn('id_acl_role', 'integer')
            ->setLength(10)
            ->setUnsigned(true);
        // id_user
        $table->addColumn('id_user', 'integer')
            ->setLength(10)
            ->setUnsigned(true);

        $table->setPrimaryKey(array('id_acl_role', 'id_user'));

        $table->addIndex(array('id_acl_role'), 'fk_acl_role_role');
        $table->addForeignKeyConstraint(
            'oft_acl_roles',
            array('id_acl_role'),
            array('id_acl_role'),
            array('onDelete' => 'no action', 'onUpdate' => 'no action'),
            'fk_acl_role_role'
        );
        $table->dropIndex('fk_acl_role_role');

        $table->addIndex(array('id_user'), 'fk_acl_role_user');
        $table->addForeignKeyConstraint(
            'oft_users',
            array('id_user'),
            array('id_user'),
            array('onDelete' => 'no action', 'onUpdate' => 'no action'),
            'fk_acl_role_user'
        );
    }

    /**
     * Création de la table oft_acl_resources
     *
     * @param Schema $schema
     */
    protected function createTableOftAclResources(Schema $schema)
    {
        $table = $schema->createTable('oft_acl_resources');

        $table->addOption('collate', 'utf8_general_ci');

        // id_acl_resource
        $table->addColumn('id_acl_resource', 'integer')
            ->setLength(10)
            ->setUnsigned(true)
            ->setAutoincrement(true);
        // name
        $table->addColumn('name', 'string')
            ->setLength(150)
            ->setUnsigned(true);

        $table
            ->setPrimaryKey(array('id_acl_resource'))
            ->addUniqueIndex(array('name'), 'ux_acl_resource_name');
    }

    /**
     * Création de la table oft_acl_role_resource
     *
     * @param Schema $schema
     */
    protected function createTableOftAclRoleResource(Schema $schema)
    {
        $table = $schema->createTable('oft_acl_role_resource');

        $table->addOption('collate', 'utf8_general_ci');

        // id_acl_role
        $table->addColumn('id_acl_role', 'integer')
            ->setLength(10)
            ->setUnsigned(true);
        // id_acl_resource
        $table->addColumn('id_acl_resource', 'integer')
            ->setLength(10)
            ->setUnsigned(true);

        $table->setPrimaryKey(array('id_acl_role', 'id_acl_resource'));

        $table->addIndex(array('id_acl_role'), 'fk_acl_roles_role');
        $table->addForeignKeyConstraint(
            'oft_acl_roles',
            array('id_acl_role'),
            array('id_acl_role'),
            array('onDelete' => 'no action', 'onUpdate' => 'no action'),
            'fk_acl_roles_role'
        );
        $table->dropIndex('fk_acl_roles_role');

        $table->addIndex(array('id_acl_resource'), 'fk_acl_roles_ressource');
        $table->addForeignKeyConstraint(
            'oft_acl_resources',
            array('id_acl_resource'),
            array('id_acl_resource'),
            array('onDelete' => 'no action', 'onUpdate' => 'no action'),
            'fk_acl_roles_ressource'
        );
    }

}
