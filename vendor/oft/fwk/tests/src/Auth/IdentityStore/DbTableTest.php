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

namespace Oft\Test\Auth\IdentityStore;

use Oft\Auth\IdentityStore\DbTable;
use PHPUnit_Framework_TestCase;

class MockEntityUserForTestGetIdentityWithUnknownUser
{

    public function getWhere()
    {
        return array();
    }

}

class MockEntityUserForTestGetIdentityWithTooManyUser
{

    public function getWhere()
    {
        return array('user1', 'user1');
    }

}

class MockEntityUserForTestGetIdentityWithBadPassword
{

    public function getWhere()
    {
        return array(
            array('id_user' => 'user1')
        );
    }

    public function load()
    {

    }

    public function getArrayForIdentity()
    {
        return array(
            'salt' => 'badSalt',
            'password' => 'badPassword'
        );
    }

}

class MockEntityUserForTestGetIdentity
{

    public function getWhere()
    {
        return array(
            array('id_user' => 'user1')
        );
    }

    public function load()
    {

    }

    public function getArrayForIdentity()
    {
        return array(
            'salt' => '7760da',
            'password' => '0debb409118c154480433b8e082fe1ea',
            'active' => '1'
        );
    }

}

class MockEntityUserForTestGetIdentityInactiveUser
{

    public function getWhere()
    {
        return array(
            array('id_user' => 'user1')
        );
    }

    public function load()
    {

    }

    public function getArrayForIdentity()
    {
        return array(
            'salt' => '7760da',
            'password' => '0debb409118c154480433b8e082fe1ea',
            'active' => '0'
        );
    }

}

class DbTableTest extends PHPUnit_Framework_TestCase
{

    public function getStore()
    {
        $db = \Mockery::mock('Doctrine\DBAL\Connection');

        $app = new \Oft\Mvc\Application();
        $app->setService('Db', $db);

        return new DbTable($app);
    }

    public function getStoreFor($username, $result)
    {

    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage User doesn't exists
     */
    public function testGetIdentityWithUnknownUser()
    {
        $username = 'unknown';
        $result = false;

        $statement = \Mockery::mock('Doctrine\DBAL\Statement');
        $statement->shouldReceive('fetch')
            ->andReturn($result);

        $queryBuilder = \Mockery::mock('Doctrine\DBAL\Query\QueryBuilder');

        // User selection
        $queryBuilder->shouldReceive('select')
            ->with('id_user', 'username', 'password', 'salt', 'token', 'token_date', 'active', 'preferred_language', 'civility', 'givenname', 'surname', 'mail', 'entity', 'manager_username', 'creation_date', 'update_time')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->with('oft_users', 'u')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->with('username = :username')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->with('username', $username)
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn($statement);

        $db = \Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $app = new \Oft\Mvc\Application();
        $app->setService('Db', $db);

        $dbTable = new DbTable($app);

        $dbTable->getIdentity($username);
    }

    public function testGetIdentity()
    {
        $username = 'user1';
        $id_user = 45;

        $user = array(
            'id_user' => $id_user,
            'username' => $username,
            'givenname' => 'Some',
            'surname' => 'Name',
            'active' => 1,
            'civility' => 0,
            'mail' => null,
            'entity' => null,
            'manager_username' => null,
            'creation_date' => null,
            'update_time' => null,
            'preferred_language' => 'ok',
            'password' => 'pass',
            'salt' => 'salt'
        );

        $groups = new \ArrayObject();
        $groups[0] = array(
            'name' => 'gp1',
            'fullname' => 'Gp1'
        );

        $statement = \Mockery::mock('Doctrine\DBAL\Statement');
        $statement->shouldReceive('fetch')
            ->andReturn($user);
        $statement->shouldReceive('getIterator')
            ->andReturn($groups);

        $queryBuilder = \Mockery::mock('Doctrine\DBAL\Query\QueryBuilder');

        // User selection
        $queryBuilder->shouldReceive('select')
            ->with('id_user', 'username', 'password', 'salt', 'token', 'token_date', 'active', 'preferred_language', 'civility', 'givenname', 'surname', 'mail', 'entity', 'manager_username', 'creation_date', 'update_time')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->with('oft_users', 'u')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->with('username = :username')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->with('username', $username)
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn($statement);

        // Group selection
        $queryBuilder->shouldReceive('select')
            ->with('r.name', 'r.fullname')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->with('oft_acl_role_user', 'ru')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('innerJoin')
            ->with('ru', 'oft_acl_roles', 'r', 'r.id_acl_role = ru.id_acl_role')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->with('id_user = :id_user')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->with('id_user', $id_user)
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn($statement);

        $db = \Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('createQueryBuilder')
            ->andReturn($queryBuilder);

        $app = new \Oft\Mvc\Application();
        $app->setService('Db', $db);

        $dbTable = new DbTable($app);
        $identity = $dbTable->getIdentity('user1');

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);
        $this->assertSame($username, $identity->username);
        $this->assertSame('Some Name', $identity->displayName);
        $this->assertSame(array('gp1' => 'Gp1', 'guests' => 'Invité'), $identity->groups);
    }
}
