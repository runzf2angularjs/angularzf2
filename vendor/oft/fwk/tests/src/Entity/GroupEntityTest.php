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
use Oft\Entity\GroupEntity;
use PHPUnit_Framework_TestCase;

class GroupEntityTest extends PHPUnit_Framework_TestCase
{

    public function testDefaultCreate()
    {
        $entity = new GroupEntity();
        $data = $entity->getArrayCopy();

        $this->assertArrayHasKey('id_acl_role', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('fullname', $data);
    }

    public function testLoad()
    {
        $queryBuilder = $this->getQueryBuilder(array(
            'id_acl_role' => 1,
            'name' => 'testName',
            'fullname' => 'testFullname'
        ));

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new GroupEntity($connection);
        $entity->load(1);
        $data = $entity->getArrayCopy();

        $this->assertEquals(1, $data['id_acl_role']);
        $this->assertEquals('testName', $data['name']);
        $this->assertEquals('testFullname', $data['fullname']);
    }

    /**
     * @expectedException \DomainException
     */
    public function testLoadFailed()
    {
        $queryBuilder = $this->getQueryBuilder(false);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new GroupEntity($connection);
        $entity->load(1);
    }

    public function testInputFiltersAreSetCorrectly()
    {
        $entity = new GroupEntity();
        $inputFilter = $entity->getInputFilter();

        $this->assertSame(3, $inputFilter->count());
        $this->assertTrue($inputFilter->has('id_acl_role'));
        $this->assertTrue($inputFilter->has('name'));
        $this->assertTrue($inputFilter->has('fullname'));
    }

    public function testSetInputFilter()
    {
        $mockInputFilter = Mockery::mock('\Zend\InputFilter\InputFilterInterface');

        $entity = new GroupEntity();
        $entity->setInputFilter($mockInputFilter);
        $inputFilter = $entity->getInputFilter();

        $this->assertEquals($mockInputFilter, $inputFilter);
    }

    public function testExchangeArray()
    {
        $data = array(
            'id_acl_role' => 2,
            'name' => 'testName',
            'fullname' => 'testFullname',
        );

        $entity = new GroupEntity();
        $entity->exchangeArray($data);
        $entityData = $entity->getArrayCopy();

        $this->assertEquals(2, $entityData['id_acl_role']);
        $this->assertEquals('testName', $entityData['name']);
        $this->assertEquals('testFullname', $entityData['fullname']);
    }

    protected function getQueryBuilder($param)
    {
        $statement = Mockery::mock('\Doctrine\DBAL\Driver\Statement');
        $statement->shouldReceive('fetch')
            ->once()
            ->withNoArgs()
            ->andReturn($param);

        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn($statement);

        return $queryBuilder;
    }

    public function testInsert()
    {
        $data = array(
            'name' => 'testName',
            'fullname' => 'testFullname',
        );

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('insert')
            ->withArgs(array('oft_acl_roles', $data))
            ->andReturn(true);

        $entity = new GroupEntity($connection);
        $entity->exchangeArray($data);
        $entity->save();
    }

    /**
     * @expectedException \DomainException
     */
    public function testFailedInsert()
    {
        $data = array(
            'name' => '',
            'fullname' => '',
        );

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('insert')
            ->withArgs(array('oft_acl_roles', $data))
            ->andReturn(false);

        $entity = new GroupEntity($connection);
        $entity->exchangeArray($data);
        $entity->save();
    }

    public function testUpdate()
    {
        $data = array(
            'id_acl_role' => 1,
            'fullname' => 'testFullname',
        );

        $queryBuilder = $this->getQueryBuilder($data);
        $queryBuilder->shouldReceive('update')
            ->withArgs(array('oft_acl_roles'))
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('set')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameters')
            ->with($data)
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn(true);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);


        $entity = new GroupEntity($connection);
        $entity->load(1);
        $entity->save();
    }

    /**
     * @expectedException \DomainException
     */
    public function testFailedUpdate()
    {
        $data = array(
            'id_acl_role' => 1,
            'fullname' => '',
        );

        $queryBuilder = $this->getQueryBuilder($data);
        $queryBuilder->shouldReceive('update')
            ->withArgs(array('oft_acl_roles'))
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('set')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameters')
            ->with($data)
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn(false);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);


        $entity = new GroupEntity($connection);
        $entity->load(1);
        $entity->save();
    }

    public function testDelete()
    {
        $data = array(
            'id_acl_role' => 1
        );

        $queryBuilder = $this->getQueryBuilder($data);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);
        $connection->shouldReceive('delete')
            ->withArgs(array("oft_acl_roles", $data))
            ->andReturn($queryBuilder);

        $entity = new GroupEntity($connection);
        $entity->load(1);
        $entity->delete();
    }

    /**
     * @expectedException \DomainException
     */
    public function testFailedDelete()
    {
        $data = array(
            'id_acl_role' => 1
        );

        $queryBuilder = $this->getQueryBuilder($data);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);
        $connection->shouldReceive('delete')
            ->withArgs(array("oft_acl_roles", $data))
            ->andReturn(false);

        $entity = new GroupEntity($connection);
        $entity->load(1);
        $entity->delete();
    }
    
    public function testDeleteGroupResources()
    {
        $data = array(
            'id_acl_role' => 1
        );

        $queryBuilder = $this->getQueryBuilder($data);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);
        $connection->shouldReceive('delete')
            ->withArgs(array("oft_acl_role_resource", $data))
            ->andReturn($queryBuilder);

        $entity = new GroupEntity($connection);
        $entity->load(1);
        $entity->deleteGroupResources();
    }

    /**
     * @expectedException \DomainException
     */
    public function testFailedDeleteGroupResources()
    {
        $data = array(
            'id_acl_role' => 1
        );

        $queryBuilder = $this->getQueryBuilder($data);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);
        $connection->shouldReceive('delete')
            ->withArgs(array("oft_acl_role_resource", $data))
            ->andReturn(false);

        $entity = new GroupEntity($connection);
        $entity->load(1);
        $entity->deleteGroupResources();
    }

    public function testHasRole()
    {
        $data = array(
            'id_acl_role' => 1
        );

        $queryBuilder = $this->getQueryBuilder($data);
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new GroupEntity($connection);
        $this->assertTrue($entity->hasGroup());

        $queryBuilderFalse = $this->getQueryBuilder(false);
        $connectionFalse = Mockery::mock('\Doctrine\DBAL\Connection');
        $connectionFalse->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilderFalse);

        $entityFalse = new GroupEntity($connectionFalse);
        $this->assertFalse($entityFalse->hasGroup());
    }
    
    public function testIsUsed()
    {
        $data = array(
            'id_acl_role' => 1
        );

        $queryBuilder = $this->getQueryBuilder($data);
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new GroupEntity($connection);
        $this->assertTrue($entity->isUsed());

        $queryBuilderFalse = $this->getQueryBuilder(false);
        $connectionFalse = Mockery::mock('\Doctrine\DBAL\Connection');
        $connectionFalse->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilderFalse);

        $entityFalse = new GroupEntity($connectionFalse);
        $this->assertFalse($entityFalse->isUsed());
    }

    public function testIsDisallowTrue()
    {
        $data = array('name' => 'administrators');
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');

        $entity = new GroupEntity($connection);
        $entity->exchangeArray($data);

        $disallow = $entity->isDisallow();

        $this->assertTrue($disallow);
    }

    public function testIsDisallowFalse()
    {
        $data = array('name' => 'test');
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');

        $entity = new GroupEntity($connection);
        $entity->exchangeArray($data);

        $disallow = $entity->isDisallow();

        $this->assertFalse($disallow);
    }

    public function testGetWhere()
    {
        $object = new \ArrayObject();
        $object[0]['name'] = 'guest';
        $object[1]['name'] = 'administrators';
        
        $statement = \Mockery::mock('\Doctrine\DBAL\Driver\Statement');
        $statement->shouldReceive('getIterator')
            ->andReturn($object);

        $queryBuilder = \Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('orderBy')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn($statement);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new GroupEntity($connection);
        
        $this->assertEquals(array(0 => array('name' => 'guest')), $entity->fetchAllExceptAdmin());
    }

    public function testGetWhereWithoutRole()
    {        
        $statement = \Mockery::mock('\Doctrine\DBAL\Driver\Statement');
        $statement->shouldReceive('fetch')
            ->andReturn(new \ArrayObject());

        $queryBuilder = \Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('orderBy')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn($statement);


        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new GroupEntity($connection);
        $this->assertEquals($statement, $entity->fetchAll());
    }

    public function testGetByName()
    {
        $data = array('test');

        $statement = Mockery::mock('\Doctrine\DBAL\Driver\Statement');
        $statement->shouldReceive('fetch')
            ->once()
            ->withNoArgs()
            ->andReturn($data);

        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('orderBy')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn($statement);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->once()
            ->andReturn($queryBuilder);

        $entity = new GroupEntity($connection);
        $this->assertEquals($data, $entity->getByName('test'));
    }

    /**
     * @expectedException \DomainException
     */
    public function testGetByNameWithFailed()
    {
        $statement = Mockery::mock('\Doctrine\DBAL\Driver\Statement');
        $statement->shouldReceive('fetch')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('where')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('orderBy')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn($statement);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->once()
            ->andReturn($queryBuilder);

        $entity = new GroupEntity($connection);
        $entity->getByName('test');
    }

    public function testGetQueryBuilder()
    {
        $whereOption = array(
            array(
                'field' => 'name',
                'value' => 'test',
            ),
            array(
                'field' => 'fullname',
                'operator' => 'LIKE',
                'value' => 'test2',
            ),
        );

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

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->once()
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new GroupEntity($connection);
        $entity->getQueryBuilder($whereOption);
    }

    public function testGetSelectValues()
    {
        $object = new \ArrayObject();
        $object[0]['id_acl_role'] = '1';
        $object[0]['name'] = 'test';
        
        $statement = \Mockery::mock('\Doctrine\DBAL\Driver\Statement');
        $statement->shouldReceive('getIterator')
            ->andReturn($object);

        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
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

        $entity = new GroupEntity($connection);
        $result = $entity->getSelectValues('id_acl_role', 'name');
        
        $this->assertEquals(array('1' => 'test'), $result);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetSelectValuesException()
    {
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');

        $entity = new GroupEntity($connection);
        $entity->getSelectValues();
    }

}
