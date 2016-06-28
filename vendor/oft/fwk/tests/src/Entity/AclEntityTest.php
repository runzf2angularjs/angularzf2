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
use Oft\Entity\AclEntity;
use PHPUnit_Framework_TestCase;

class AclEntityTest extends PHPUnit_Framework_TestCase
{

    protected $connection;
    
    protected function setUp()
    {
        $this->connection = \Mockery::mock('\Doctrine\DBAL\Connection');
    }
    
    public function testDefaultCreate()
    {
        $entity = new AclEntity();
        $data = $entity->getArrayCopy();

        $this->assertArrayHasKey('id_acl_role', $data);
        $this->assertArrayHasKey('id_acl_resource', $data);
    }

    public function testLoad()
    {
        $queryBuilder = $this->getQueryBuilder(array(
            'id_acl_role' => 1,
            'id_acl_resource' => 2,
        ));

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new AclEntity($connection);
        $entity->load(1, 2);
        $data = $entity->getArrayCopy();

        $this->assertEquals(1, $data['id_acl_role']);
        $this->assertEquals(2, $data['id_acl_resource']);
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

        $entity = new AclEntity($connection);
        $entity->load(1, 2);
    }

    public function testInputFiltersAreSetCorrectly()
    {
        $entity = new AclEntity();
        $inputFilter = $entity->getInputFilter();

        $this->assertSame(2, $inputFilter->count());
        $this->assertTrue($inputFilter->has('id_acl_resource'));
        $this->assertTrue($inputFilter->has('id_acl_role'));
    }

    public function testSetInputFilter()
    {
        $mockInputFilter = Mockery::mock('\Zend\InputFilter\InputFilterInterface');

        $entity = new AclEntity();
        $entity->setInputFilter($mockInputFilter);
        $inputFilter = $entity->getInputFilter();

        $this->assertEquals($mockInputFilter, $inputFilter);
    }

    public function testExchangeArray()
    {
        $data = array(
            'id_acl_resource' => 2,
            'id_acl_role' => 1,
        );

        $entity = new AclEntity();
        $entity->exchangeArray($data);
        $entityData = $entity->getArrayCopy();

        $this->assertEquals(2, $entityData['id_acl_resource']);
        $this->assertEquals(1, $entityData['id_acl_role']);
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
        $queryBuilder->shouldReceive('andWhere')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('setParameter')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('orderBy')
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn($statement);

        return $queryBuilder;
    }

    public function testInsert()
    {
        $data = array(
            'id_acl_resource' => 2,
            'id_acl_role' => 1,
        );

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('insert')
            ->andReturn(true);

        $entity = new AclEntity($connection);
        $entity->exchangeArray($data);
        $entity->insert();
    }

    /**
     * @expectedException \DomainException
     */
    public function testFailedInsert()
    {
        $data = array(
            'id_acl_resource' => 2,
            'id_acl_role' => 1,
        );

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('insert')
            ->andReturn(false);

        $entity = new AclEntity($connection);
        $entity->exchangeArray($data);
        $entity->insert();
    }

    public function testDelete()
    {
        $data = array(
            'id_acl_resource' => 2,
            'id_acl_role' => 1,
        );

        $queryBuilder = $this->getQueryBuilder($data);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);
        $connection->shouldReceive('delete')
            ->andReturn($queryBuilder);

        $entity = new AclEntity($connection);
        $entity->load(1, 2);
        $entity->delete();
    }

    /**
     * @expectedException \DomainException
     */
    public function testFailedDelete()
    {
        $data = array(
            'id_acl_resource' => 2,
            'id_acl_role' => 1,
        );

        $queryBuilder = $this->getQueryBuilder($data);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);
        $connection->shouldReceive('delete')
            ->andReturn(false);

        $entity = new AclEntity($connection);
        $entity->load(1, 2);
        $entity->delete();
    }

    public function testHasAcl()
    {
        $data = array(
            'id_acl_resource' => 2,
            'id_acl_role' => 1,
        );

        $queryBuilder = $this->getQueryBuilder($data);
        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new AclEntity($connection);
        $this->assertTrue($entity->hasAcl());

        $queryBuilderFalse = $this->getQueryBuilder(false);
        $connectionFalse = Mockery::mock('\Doctrine\DBAL\Connection');
        $connectionFalse->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilderFalse);

        $entityFalse = new AclEntity($connectionFalse);
        $this->assertFalse($entityFalse->hasAcl());
    }

    public function testAll()
    {
        $statement = \Mockery::mock('\Doctrine\DBAL\Driver\Statement');
        
        $queryBuilder = \Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn($statement);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $aclEntity = new AclEntity($connection);
        
        $this->assertEquals($statement, $aclEntity->fetchAll());
    }

    public function testGetAllWithoutGroup()
    {
        $statement = \Mockery::mock('\Doctrine\DBAL\Driver\Statement');

        $queryBuilder = \Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->once()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn($statement);


        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $aclEntity = new AclEntity($connection);

        $this->assertEquals($statement, $aclEntity->fetchAll());
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


        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->once()
            ->withNoArgs()
            ->andReturn($queryBuilder);

        $entity = new AclEntity($connection);
        $entity->getQueryBuilder($whereOption);
    }

}
