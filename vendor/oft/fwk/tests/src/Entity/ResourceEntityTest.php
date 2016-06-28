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
use Oft\Entity\ResourceEntity;
use PHPUnit_Framework_TestCase;

class ResourceEntityTest extends PHPUnit_Framework_TestCase
{

    public function testDefaultCreate()
    {
        $entity = new ResourceEntity();
        $data = $entity->getArrayCopy();

        $this->assertArrayHasKey('id_acl_resource', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('controller', $data);
        $this->assertArrayHasKey('module', $data);
        $this->assertArrayHasKey('action', $data);
    }

    public function testLoad()
    {
        $queryBuilder = $this->getQueryBuilder(array(
            'id_acl_resource' => 1,
            'name' => 'mvc.module.controller.action',
        ));

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new ResourceEntity($connection);
        $entity->load(1);
        $data = $entity->getArrayCopy();

        $this->assertEquals(1, $data['id_acl_resource']);
        $this->assertEquals('mvc.module.controller.action', $data['name']);
        $this->assertEquals('mvc', $data['type']);
        $this->assertEquals('module', $data['module']);
        $this->assertEquals('controller', $data['controller']);
        $this->assertEquals('action', $data['action']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetAclDataException()
    {
        $entity = new ResourceEntity();
        $entity->setAclData('mvcerror.module.controller.action');
    }

    public function testSetAclDataWithModule()
    {
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $entity = new ResourceEntity($connection);
        $entity->setAclData('mvc.module');

        $data = $entity->getArrayCopy();

        $this->assertEquals('mvc', $data['type']);
        $this->assertEquals('module', $data['module']);
        $this->assertEquals(null, $data['controller']);
        $this->assertEquals(null, $data['action']);
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

        $entity = new ResourceEntity($connection);
        $entity->load(1);
    }

    public function testInputFiltersAreSetCorrectly()
    {
        $entity = new ResourceEntity();
        $inputFilter = $entity->getInputFilter();

        $this->assertSame(5, $inputFilter->count());
        $this->assertTrue($inputFilter->has('id_acl_resource'));
        $this->assertTrue($inputFilter->has('type'));
        $this->assertTrue($inputFilter->has('module'));
        $this->assertTrue($inputFilter->has('controller'));
        $this->assertTrue($inputFilter->has('action'));
    }

    public function testSetInputFilter()
    {
        $mockInputFilter = Mockery::mock('\Zend\InputFilter\InputFilterInterface');

        $entity = new ResourceEntity();
        $entity->setInputFilter($mockInputFilter);
        $inputFilter = $entity->getInputFilter();

        $this->assertEquals($mockInputFilter, $inputFilter);
    }

    public function testExchangeArray()
    {
        $data = array(
            'id_acl_resource' => 2,
            'type' => 'mvc',
            'module' => 'module',
            'controller' => 'controller',
            'action' => 'action',
        );

        $entity = new ResourceEntity();
        $entity->exchangeArray($data);
        $entityData = $entity->getArrayCopy();

        $this->assertEquals(2, $entityData['id_acl_resource']);
        $this->assertEquals('mvc.module.controller.action', $entityData['name']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExchangeArrayException()
    {
        $data = array(
            'type' => 'mvcError',
        );

        $entity = new ResourceEntity();
        $entity->exchangeArray($data);
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
        $queryBuilder->shouldReceive('orderBy')
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
            'type' => 'mvc',
            'module' => 'module',
            'controller' => 'controller',
            'action' => 'action',
        );

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('insert')
            ->andReturn(true);

        $entity = new ResourceEntity($connection);
        $entity->exchangeArray($data);
        $entity->save();
    }

    /**
     * @expectedException \DomainException
     */
    public function testFailedInsert()
    {
        $data = array(
            'type' => 'mvc',
            'module' => 'module',
            'controller' => 'controller',
            'action' => 'action',
        );

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('insert')
            ->andReturn(false);

        $entity = new ResourceEntity($connection);
        $entity->exchangeArray($data);
        $entity->save();
    }

    public function testUpdate()
    {
        $data = array(
            'id_acl_resource' => 1,
            'name' => 'mvc.module.controller.action',
            'type' => 'mvc',
            'module' => 'module',
            'controller' => 'controller',
            'action' => 'action',
        );

        $queryBuilder = $this->getQueryBuilder($data);
        $queryBuilder->shouldReceive('update')
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

        $entity = new ResourceEntity($connection);
        $entity->load(1);
        $entity->save();
    }

    /**
     * @expectedException \DomainException
     */
    public function testFailedUpdate()
    {
        $data = array(
            'id_acl_resource' => 1,
            'name' => 'mvc.module.controller.action',
            'type' => 'mvc',
            'module' => 'module',
            'controller' => 'controller',
            'action' => 'action',
        );

        $queryBuilder = $this->getQueryBuilder($data);
        $queryBuilder->shouldReceive('update')
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


        $entity = new ResourceEntity($connection);
        $entity->load(1);
        $entity->save();
    }

    public function testDelete()
    {
        $data = array(
            'id_acl_resource' => 1,
            'name' => 'mvc.module.controller.action',
        );

        $queryBuilder = $this->getQueryBuilder($data);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);
        $connection->shouldReceive('delete')
            ->andReturn($queryBuilder);

        $entity = new ResourceEntity($connection);
        $entity->load(1);
        $entity->delete();
    }
    
    public function testDeleteGroupResources()
    {
        $data = array(
            'id_acl_resource' => 1,
            'name' => 'mvc.module.controller.action',
        );

        $queryBuilder = $this->getQueryBuilder($data);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);
        $connection->shouldReceive('delete')
            ->withArgs(array("oft_acl_role_resource", array('id_acl_resource' => 1)))
            ->andReturn($queryBuilder);

        $entity = new ResourceEntity($connection);
        $entity->load(1);
        $entity->deleteGroupResources();
    }

    /**
     * @expectedException \DomainException
     */
    public function testFailedDelete()
    {
        $data = array(
            'id_acl_resource' => 1,
            'name' => 'mvc.module.controller.action',
        );

        $queryBuilder = $this->getQueryBuilder($data);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);
        $connection->shouldReceive('delete')
            ->andReturn(false);

        $entity = new ResourceEntity($connection);
        $entity->load(1);
        $entity->delete();
    }

    public function testHasResource()
    {
        $data = array(
            'id_acl_role' => 1
        );

        $queryBuilder = $this->getQueryBuilder($data);
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new ResourceEntity($connection);
        $this->assertTrue($entity->hasResource());

        $queryBuilderFalse = $this->getQueryBuilder(false);
        $connectionFalse = Mockery::mock('\Doctrine\DBAL\Connection');
        $connectionFalse->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilderFalse);

        $entityFalse = new ResourceEntity($connectionFalse);
        $this->assertFalse($entityFalse->hasResource());
    }

    public function testGetWhere()
    {
        $statement = \Mockery::mock('\Doctrine\DBAL\Driver\Statement');

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

        $entity = new ResourceEntity($connection);

        $this->assertEquals($statement, $entity->fetchAll());
    }

    public function testGetWhereWithoutGroup()
    {
        $statement = \Mockery::mock('\Doctrine\DBAL\Driver\Statement');

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

        $entity = new ResourceEntity($connection);
        $this->assertEquals($statement, $entity->fetchAll(array()));
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

        $entity = new ResourceEntity($connection);
        $entity->getQueryBuilder($whereOption);
    }

}
