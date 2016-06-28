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

namespace Oft\Test\Db;

use Mockery;
use Oft\Db\EntityQueryBuilder;
use PDO;
use PHPUnit_Framework_TestCase;

class EntityQueryBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testExecuteWithSelect()
    {
        $statement = Mockery::mock('Doctrine\DBAL\Driver\ResultStatement');
        $statement->shouldReceive('setFetchMode')
            ->with(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Oft\Entity\BaseEntity', array())
            ->once();

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('executeQuery')
            ->andReturn($statement);

        $queryBuilder = new EntityQueryBuilder($db);
        $queryBuilder->select();

        $result = $queryBuilder->execute();
        $this->assertSame($statement, $result);
    }

    public function testExecuteWithUpdate()
    {
        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('executeUpdate')
            ->once()
            ->andReturn(true);

        $queryBuilder = new EntityQueryBuilder($db);
        $queryBuilder->update('table')
            ->set('col', 'value');

        $result = $queryBuilder->execute();
        $this->assertTrue($result);
    }

    public function testFetchClassArgsDefaults()
    {
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        $queryBuilder = new EntityQueryBuilder($db);

        $this->assertEquals('Oft\Entity\BaseEntity', $queryBuilder->getFetchClass());
        $this->assertEquals(array(), $queryBuilder->getFetchArgs());
    }

    public function testFetchClassArgsSpecific()
    {
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        $queryBuilder = new EntityQueryBuilder($db, 'SomeEntity', array('someargs'));

        $this->assertEquals('SomeEntity', $queryBuilder->getFetchClass());
        $this->assertEquals(array('someargs'), $queryBuilder->getFetchArgs());
    }

    public function testFetchClassArgsSetters()
    {
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        $queryBuilder = new EntityQueryBuilder($db);
        $queryBuilder->setFetchClass('SomeEntity');
        $queryBuilder->setFetchArgs(array('someargs'));

        $this->assertEquals('SomeEntity', $queryBuilder->getFetchClass());
        $this->assertEquals(array('someargs'), $queryBuilder->getFetchArgs());
    }

    public function testApplyFilters()
    {
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        $filters = array(
            array(
                // uppercase 'like'
                'operator' => 'like',
                'value' => 'testv',
                'field' => 'testf'
            ),
            array(
                // operator must be '='
                'value' => 'testv2',
                'field' => 'testf2',
            ),
            array(
                // bindName
                'operator' => '<>',
                'value' => 'testv3',
                'field' => 'testv3',
                'bindName' => 'someName'
            )
        );

        $queryBuilder = new EntityQueryBuilder($db);
        $queryBuilder->applyFilters($filters);

        $queryParts = $queryBuilder->getQueryParts();
        $this->assertInstanceOf('Doctrine\DBAL\Query\Expression\CompositeExpression', $queryParts['where']);
        $this->assertEquals(\Doctrine\DBAL\Query\Expression\CompositeExpression::TYPE_AND, $queryParts['where']->getType());
        $this->assertEquals('(testf like :testf) AND (testf2 = :testf2) AND (testv3 <> :someName)', (string)$queryParts['where']);

        $queryParams = $queryBuilder->getParameters();
        $this->assertArrayHasKey('testf', $queryParams);
        $this->assertEquals('%testv%', $queryParams['testf']);
        $this->assertArrayHasKey('testf2', $queryParams);
        $this->assertEquals('testv2', $queryParams['testf2']);
        $this->assertArrayHasKey('someName', $queryParams);
        $this->assertEquals('testv3', $queryParams['someName']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A table name must be specified
     */
    public function testApplyOptionsThrowExceptionIfTableIsNotSet()
    {
        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $options = array();

        $queryBuilder = new EntityQueryBuilder($db);
        $queryBuilder->applyOptions($options);
    }

    public function testApplyOptions()
    {
        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $options = array(
            'table' => 'test',
            'alias' => 't',
            'columns' => array('t.id', 't.name'),
            'sort' => array('t.id' => 'aSc', 't.name' => 'deSc')
        );

        $queryBuilder = new EntityQueryBuilder($db);
        $queryBuilder->applyOptions($options);

        $queryParts = $queryBuilder->getQueryParts();
        $this->assertInternalType('array', $queryParts['select']);
        $this->assertEquals('t.id', $queryParts['select'][0]);
        $this->assertEquals('t.name', $queryParts['select'][1]);

        $this->assertInternalType('array', $queryParts['from']);
        $this->assertCount(1, $queryParts['from']);
        $this->assertInternalType('array', $queryParts['from'][0]);
        $this->assertCount(2, $queryParts['from'][0]);
        $this->assertEquals('test', $queryParts['from'][0]['table']);
        $this->assertEquals('t', $queryParts['from'][0]['alias']);

        $this->assertInternalType('array', $queryParts['orderBy']);
        $this->assertCount(2, $queryParts['orderBy']);
        $this->assertEquals('t.id aSc', $queryParts['orderBy'][0]);
        $this->assertEquals('t.name deSc', $queryParts['orderBy'][1]);
    }
}
