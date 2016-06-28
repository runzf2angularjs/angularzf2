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

namespace Oft\Admin\Test\Service;

use Doctrine\DBAL\Query\QueryBuilder;
use DomainException;
use Mockery;
use Oft\Admin\Service\ResourcesService;
use Oft\Mvc\Application;
use PHPUnit_Framework_TestCase;
use Zend\InputFilter\InputFilter;

class MockEntityResourceForTestGetWhere
{
    public function fetchAll($array)
    {
        return array();
    }
}

class MockEntityResourceForTestGetById
{
    public function load()
    {
        return true;
    }
    public function getArrayCopy()
    {
        return array();
    }
}

class MockEntityResourceForTestGetPaginator
{
    public function getQueryBuilder()
    {
        $qb = Mockery::mock('Doctrine\DBAL\Query\QueryBuilder');
        $qb->shouldReceive('getType')->once()->andReturn(QueryBuilder::SELECT);
        return $qb;
    }
}

class MockEntityResourceForTestGetForm
{
    public function load()
    {
        return true;
    }
    public function getInputFilter()
    {
        return new InputFilter();
    }
    public function getArrayCopy()
    {
        return array();
    }
}

class MockEntityResourceForAutocomplete
{
    public function fetchAll($where = array())
    {
        return array(
            array('field' => 'value1'),
            array('field' => 'value2'),
            array('field' => 'value3'),
        );
    }
}

class ResourcesServiceTest extends PHPUnit_Framework_TestCase
{

    protected function getService(Application $app = null)
    {
        if ($app === null) {
            $moduleManager = Mockery::mock('Oft\Module\ModuleManager');
            $moduleManager->shouldReceive('getModules')
                ->andReturn(array('module1', 'module2'));

            $app = new Application(array(), $moduleManager);
            $app->setService('Db', Mockery::mock('Doctrine\DBAL\Connection'));
        }

        return new ResourcesService($app);
    }

    public function testGetFieldsSearch()
    {
        $service = $this->getService();

        $fieldsSearch = $service->getFieldsSearch();

        $this->assertInternalType('array', $fieldsSearch);
    }

    public function testGetPaginator()
    {
        $service = $this->getService();

        $service->setEntityClassName('resource', 'Oft\Admin\Test\Service\MockEntityResourceForTestGetPaginator');

        $paginator = $service->getPaginator(array('name' => 'test'));
        
        $this->assertInstanceOf('Zend\Paginator\Paginator', $paginator);
    }

    public function testGetSearchForm()
    {
        $service = $this->getService();
        
        $form = $service->getSearchForm();

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('submitSearch'));
        $this->assertTrue($form->has('resetSearch'));

        $this->assertInstanceOf('Oft\Admin\Form\SearchForm', $form);
    }

    public function testGetForm()
    {
        $service = $this->getService();

        $form = $service->getForm();
        $resourceEntity = $form->getObject();

        $this->assertInstanceOf('Oft\Admin\Form\ResourceForm', $form);
        $this->assertInstanceOf('Oft\Entity\ResourceEntity', $resourceEntity);

        $this->assertFalse($resourceEntity->getInputFilter()->has('id_acl_resource'));
    }

    public function testGetFormWithId()
    {
        $service = $this->getService();

        $service->setEntityClassName('resource', 'Oft\Admin\Test\Service\MockEntityResourceForTestGetForm');

        $form = $service->getFormReadOnly(1);

        $this->assertInstanceOf('Oft\Admin\Form\ResourceForm', $form);

        $elements = $form->getElements();
        foreach ($elements as $element) {
            $this->assertEquals('disabled', $element->getAttribute('disabled'));
        }
    }

    public function testInsert()
    {
        $resource = Mockery::mock('Oft\Entity\ResourceEntity');
        $resource->shouldReceive('hasResource')
            ->once()
            ->andReturn(false);
        $resource->shouldReceive('save')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->insert($resource);
    }

    /**
     * @expectedException DomainException
     */
    public function testInsertWithExistingGroup()
    {
        $resource = Mockery::mock('Oft\Entity\ResourceEntity');
        $resource->shouldReceive('hasResource')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->insert($resource);
    }

    public function testUpdate()
    {
        $resource = Mockery::mock('Oft\Entity\ResourceEntity');
        $resource->shouldReceive('save')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->update($resource);
    }

    public function testDelete()
    {
        $id = 1;

        $resource = Mockery::mock('Oft\Entity\ResourceEntity');
        $resource->shouldReceive('load')
            ->with($id)
            ->once()
            ->andReturn(true);
        $resource->shouldReceive('delete')
            ->once()
            ->andReturn(true);
        $resource->shouldReceive('deleteGroupResources')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->delete($resource, $id);
    }

    public function testGetWhere()
    {
        $service = $this->getService();
        
        $service->setEntityClassName('resource', 'Oft\Admin\Test\Service\MockEntityResourceForTestGetWhere');

        $this->assertEquals(array(), $service->fetchAll());
    }
    
    public function testGetById()
    {
        $service = $this->getService();
        
        $service->setEntityClassName('resource', 'Oft\Admin\Test\Service\MockEntityResourceForTestGetById');

        $this->assertEquals(array(), $service->getById('1'));
    }

    /**
     * @expectedException DomainException
     */
    public function testAutocompleteRefused()
    {
        $service = $this->getService();

        $service->autoComplete('not-an-existing--entity', 'field', 'value');
    }

    public function testAutocomplete()
    {
        $service = $this->getService();

        $service->setEntityClassName('resource', 'Oft\Admin\Test\Service\MockEntityResourceForAutocomplete');

        $expected = array('value1', 'value2', 'value3');
        $actual = $service->autoComplete('resource', 'field', 'value');

        $this->assertEquals($expected, $actual);
    }

}
