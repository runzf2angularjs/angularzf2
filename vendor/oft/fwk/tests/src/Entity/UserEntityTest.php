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

namespace Oft\Test\Entity;

use Mockery;
use Oft\Entity\UserEntity;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class UserEntityTest extends PHPUnit_Framework_TestCase
{

    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testDefaultCreate()
    {
        $entity = new UserEntity;
        $data = $entity->getArrayCopy();

        $this->assertArrayHasKey('id_user', $data);
        $this->assertArrayHasKey('username', $data);
        $this->assertArrayHasKey('password', $data);
        $this->assertArrayHasKey('salt', $data);
        $this->assertArrayHasKey('active', $data);
        $this->assertArrayHasKey('preferred_language', $data);
        $this->assertArrayHasKey('civility', $data);
        $this->assertArrayHasKey('givenname', $data);
        $this->assertArrayHasKey('surname', $data);
        $this->assertArrayHasKey('mail', $data);
        $this->assertArrayHasKey('entity', $data);
        $this->assertArrayHasKey('manager_username', $data);
        $this->assertArrayHasKey('creation_date', $data);
        $this->assertArrayHasKey('update_time', $data);
        $this->assertArrayHasKey('groups', $data);
    }

    protected function getQueryBuilder($paramUser, $paramRole)
    {
        $statement = Mockery::mock('\Doctrine\DBAL\Driver\Statement');
        $statement->shouldReceive('fetch')
            ->withNoArgs()
            ->andReturn($paramUser);
        $statement->shouldReceive('getIterator')
            ->withNoArgs()
            ->andReturn($paramRole);

        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn($statement);

        return $queryBuilder;
    }

    protected function getQueryBuilder2($retrieveMethodName, $retrieveMethodresult)
    {
        $statement = Mockery::mock('\Doctrine\DBAL\Driver\Statement');
        $statement->shouldReceive($retrieveMethodName)
            ->withNoArgs()
            ->andReturn($retrieveMethodresult);

        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn($statement);

        return $queryBuilder;
    }

    public function testLoad()
    {
        $dataUser = array(
            'id_user' => 1,
            'username' => 'ABCD1234',
            'password' => 'testPassword',
            'salt' => '123456',
            'active' => '1',
            'preferred_language' => 'fr',
            'civility' => '1',
            'givenname' => 'testGivenname',
            'surname' => 'testSurname',
            'mail' => 'test@mail.fr',
            'entity' => 'testEntity',
            'manager_cuid' => 'ABCD5678',
            'creation_date' => '2014-08-28 10:00:00',
            'update_time' => '2014-08-29 10:00:00',
        );
        
        $object = new \ArrayObject();
        
        $dataRoles = array(
            'name' => 'testName',
            'fullname' => 'testFullname'
        );
        
        $object[0] = $dataRoles;

        $finalData = $dataUser;
        $finalData['groups'] = array('testName' => 'testFullname');
        
        $queryBuilder = $this->getQueryBuilder($dataUser, $object);
        $queryBuilder->shouldReceive('innerJoin')
            ->andReturn($queryBuilder);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new UserEntity($connection);
        $entity->load(1);
        $data = $entity->getArrayCopy();

        $this->assertEquals($finalData, $data);
    }

    /**
     * @expectedException \DomainException
     */
    public function testConstructorWithLoadFailed()
    {
        $queryBuilder = $this->getQueryBuilder(false, false);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new UserEntity($connection);
        $entity->load(1);
    }

    public function testInputFiltersAreSetCorrectly()
    {
        $entity = new UserEntity;
        $inputFilter = $entity->getInputFilter();

        $this->assertSame(13, $inputFilter->count());
        $this->assertTrue($inputFilter->has('id_user'));
        $this->assertTrue($inputFilter->has('username'));
        $this->assertTrue($inputFilter->has('password'));
        $this->assertTrue($inputFilter->has('password_confirm'));
        $this->assertTrue($inputFilter->has('active'));
        $this->assertTrue($inputFilter->has('preferred_language'));
        $this->assertTrue($inputFilter->has('civility'));
        $this->assertTrue($inputFilter->has('givenname'));
        $this->assertTrue($inputFilter->has('surname'));
        $this->assertTrue($inputFilter->has('mail'));
        $this->assertTrue($inputFilter->has('entity'));
        $this->assertTrue($inputFilter->has('manager_username'));
        $this->assertTrue($inputFilter->has('groups'));
    }

    public function testSetInputFilter()
    {
        $mockInputFilter = Mockery::mock('\Zend\InputFilter\InputFilterInterface');

        $entity = new UserEntity();
        $entity->setInputFilter($mockInputFilter);
        $inputFilter = $entity->getInputFilter();

        $this->assertEquals($mockInputFilter, $inputFilter);
    }

    public function testExchangeArray()
    {
        $data = array(
            'id_user' => 2,
            'username' => 'cuidtest',
            'password' => '1234',
            'salt' => '123',
            'token' => 'test',
            'token_date' => '2014-07-10 00:00:00',
            'active' => '1',
            'preferred_language' => 'FR',
            'civility' => '2',
            'givenname' => 'toto',
            'surname' => 'tata',
            'mail' => 'email',
            'entity' => 'test',
            'manager_username' => 'cuidtest2',
            'creation_date' => '2014-07-11 00:00:00',
            'update_time' => '2014-07-11 12:00:00',
        );

        $entity = new UserEntity;
        $entity->exchangeArray($data);
        $entityData = $entity->getArrayCopy();

        $this->assertEquals(2, $entityData['id_user']);
        $this->assertEquals('cuidtest', $entityData['username']);
        $this->assertNotEquals('1234', $entityData['password']);
        $this->assertEquals(32, strlen($entityData['password']));
        $this->assertNotEquals('123', $entityData['salt']);
        $this->assertEquals('test', $entityData['token']);
        $this->assertEquals('2014-07-10 00:00:00', $entityData['token_date']);
        $this->assertEquals('1', $entityData['active']);
        $this->assertEquals('FR', $entityData['preferred_language']);
        $this->assertEquals('2', $entityData['civility']);
        $this->assertEquals('toto', $entityData['givenname']);
        $this->assertEquals('tata', $entityData['surname']);
        $this->assertEquals('email', $entityData['mail']);
        $this->assertEquals('test', $entityData['entity']);
        $this->assertEquals('cuidtest2', $entityData['manager_username']);
        $this->assertEquals('2014-07-11 00:00:00', $entityData['creation_date']);
        $this->assertEquals('2014-07-11 12:00:00', $entityData['update_time']);
    }

    public function testUpdate()
    {
        $dataUser = array(
            'id_user' => 1,
            'username' => 'ABCD1234',
            'password' => 'testPassword',
            'salt' => '123456',
            'active' => '1',
            'preferred_language' => 'fr',
            'civility' => '1',
            'givenname' => 'testGivenname',
            'surname' => 'testSurname',
            'mail' => 'test@mail.fr',
            'entity' => 'testEntity',
            'manager_cuid' => 'ABCD5678',
            'creation_date' => '2014-08-28 10:00:00',
            'update_time' => '2014-08-29 10:00:00',
        );

        $dataRoles = array(
            'name' => 'testName',
            'fullname' => 'testFullname'
        );
        
        $object = new \ArrayObject();
        $object[0] = $dataRoles;

        $queryBuilder = $this->getQueryBuilder($dataUser, $object);
        $queryBuilder->shouldReceive('innerJoin')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('update')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('set')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameters')
            ->andReturn($queryBuilder);


        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);
        $connection->shouldReceive('lastInsertId')
            ->andReturn(1);

        $entity = new UserEntity($connection);
        $entity->load(1);
        $entity->save();
    }

    /**
     * @expectedException \DomainException
     */
    public function testUpdateFailed()
    {
        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('update')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('set')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameters')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn(false);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new UserEntity($connection);
        $this->invokeMethod($entity, 'update');
    }

    public function testInsert()
    {
        $dataUser = array(
            'username' => 'ABCD1234',
            'password' => 'testPassword',
            'salt' => '123456',
            'active' => '1',
            'preferred_language' => 'fr',
            'civility' => '1',
            'givenname' => 'testGivenname',
            'surname' => 'testSurname',
            'mail' => 'test@mail.fr',
            'entity' => 'testEntity',
            'manager_cuid' => 'ABCD5678',
            'creation_date' => '2014-08-28 10:00:00',
            'update_time' => '2014-08-29 10:00:00',
        );

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('insert')
            ->andReturn(true);
        $connection->shouldReceive('lastInsertId')
            ->andReturn(1);

        $entity = new UserEntity($connection);
        $entity->exchangeArray($dataUser);
        $entity->save();
    }

    /**
     * @expectedException \DomainException
     */
    public function testInsertFailed()
    {
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('insert')
            ->andReturn(false);

        $entity = new UserEntity($connection);
        $this->invokeMethod($entity, 'insert');
    }

    public function testRemoveRole()
    {
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('delete')
            ->andReturn(true);

        $entity = new UserEntity($connection);
        $entity->removeGroup('testName');
    }

    /**
     * @expectedException \DomainException
     */
    public function testRemoveRoleFailed()
    {
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('delete')
            ->andReturn(false);

        $entity = new UserEntity($connection);
        $entity->removeGroup('testName');
    }

    public function testAddRole()
    {
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('insert')
            ->andReturn(true);

        $entity = new UserEntity($connection);
        $entity->addGroup('testName');
    }

    /**
     * @expectedException \DomainException
     */
    public function testAddRoleFailed()
    {
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('insert')
            ->andReturn(false);

        $entity = new UserEntity($connection);
        $entity->addGroup('testName');
    }

    public function testGetAll()
    {        
        $statement = Mockery::mock('\Doctrine\DBAL\Driver\Statement');
        
        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('orderBy')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn($statement);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new UserEntity($connection);
        $this->assertEquals($statement, $entity->fetchAll());
    }

    public function testHasUserIsTrue()
    {
        $this->assertTrue($this->getResultHasUser(array()));
    }

    public function testHasUserIsFalse()
    {
        $this->assertFalse($this->getResultHasUser(false));
    }

    protected function getResultHasUser($param)
    {
        $queryBuilder = $this->getQueryBuilder2('fetch', $param);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->once()
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new UserEntity($connection);
        return $entity->hasUser();
    }

    public function testDelete()
    {
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('delete')
            ->andReturn(true);

        $entity = new UserEntity($connection);
        $entity->delete();
    }

    /**
     * @expectedException \DomainException
     */
    public function testDeleteFailed()
    {
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('delete')
            ->andReturn(false);

        $entity = new UserEntity($connection);
        $entity->delete();
    }

    public function testGetWhere()
    {
       $whereOption = array(
            array(
                'field' => 'mail',
                'value' => 'test',
            ),
            array(
                'field' => 'username',
                'operator' => 'LIKE',
                'value' => 'test2',
            ),
        );

        $statement = Mockery::mock('\Doctrine\DBAL\Driver\Statement');
        $statement->shouldReceive('fetchAll')
            ->withNoArgs()
            ->andReturn(array());

        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('andWhere')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('orderBy')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn($statement);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->once()
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new UserEntity($connection);
        $entity->getWhere($whereOption);
    }

    public function testGetArrayForIdentity()
    {
        $data = array(
            'username' => 'cuid',
            'givenname' => 'givenname',
            'surname' => 'surname',
            'active' => '1',
            'preferred_language' => '1',
            'groups' => array('admin')
        );

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $entity = new UserEntity($connection);

        $entity->exchangeArray($data);
        $dataEntity = $entity->getArrayForIdentity();

        $this->assertEquals($data['username'], $dataEntity['username']);
        $this->assertEquals($data['givenname'] . ' ' . $data['surname'], $dataEntity['displayName']);
        $this->assertEquals($data['active'], $dataEntity['active']);
        $this->assertEquals($data['preferred_language'], $dataEntity['language']);
        $this->assertTrue(isset($dataEntity['password']));
        $this->assertTrue(isset($dataEntity['salt']));
        $this->assertEquals($data['groups'], $dataEntity['groups']);
    }

    public function testGenerateToken()
    {
        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('update')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('set')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn(array());

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->once()
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new UserEntity($connection);
        $entity->generateToken();
    }
    
    public function testResetToken()
    {
        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('update')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('set')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn(array());

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->once()
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new UserEntity($connection);
        $entity->resetToken();
    }

    /**
     * @expectedException \DomainException
     */
    public function testGenerateTokenFailed()
    {
        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('update')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('set')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn(false);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->once()
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new UserEntity($connection);
        $entity->generateToken();
    }
    
    /**
     * @expectedException \DomainException
     */
    public function testResetTokenFailed()
    {
        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('update')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('set')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->andReturn(false);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->once()
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new UserEntity($connection);
        $entity->resetToken();
    }

}
