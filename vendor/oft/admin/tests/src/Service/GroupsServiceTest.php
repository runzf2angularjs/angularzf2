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
use Oft\Admin\Service\GroupsService;
use Oft\Mvc\Application;
use PHPUnit_Framework_TestCase;
use Zend\InputFilter\InputFilter;

class MockEntityForTestGetPaginator
{
    public function getQueryBuilder()
    {
        $qb = Mockery::mock('Doctrine\DBAL\Query\QueryBuilder');
        $qb->shouldReceive('getType')->once()->andReturn(QueryBuilder::SELECT);
        return $qb;
    }
}

class MockEntityGroupForTestGetForm
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

class MockEntityGroupForTestGetWhere
{
    public function fetchAll($array)
    {
        return array();
    }
}

class MockEntityGroupForTestGetWhereExceptAdmin
{
    public function fetchAllExceptAdmin($array)
    {
        return array();
    }
}

class MockEntityGroupForTestGetById
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

class MockEntityGroupsForAutocomplete
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

class GroupsServiceTest extends PHPUnit_Framework_TestCase
{

    protected function getService(Application $app = null)
    {
        if ($app === null) {
            $app = new Application();
            $app->setService('Db', Mockery::mock('Doctrine\DBAL\Connection'));
        }

        return new GroupsService($app);
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

        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityForTestGetPaginator');

        $paginator = $service->getPaginator(array('name' => 'test', 'name2' => 'test'));
        
        $this->assertInstanceOf('Zend\Paginator\Paginator', $paginator);
    }

    public function testGetSearchForm()
    {
        $service = $this->getService();
        
        $form = $service->getSearchForm();

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('fullname'));
        $this->assertTrue($form->has('submitSearch'));
        $this->assertTrue($form->has('resetSearch'));

        $this->assertInstanceOf('Oft\Admin\Form\SearchForm', $form);
    }

    public function testGetForm()
    {
        $service = $this->getService();

        $form = $service->getForm();
        $userEntity = $form->getObject();

        $this->assertInstanceOf('Oft\Admin\Form\GroupForm', $form);
        $this->assertInstanceOf('Oft\Entity\GroupEntity', $userEntity);

        $this->assertFalse($userEntity->getInputFilter()->has('id_acl_role'));
    }

    public function testGetFormWithId()
    {
        $service = $this->getService();
        
        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityGroupForTestGetForm');

        $form = $service->getFormReadOnly(1);

        $this->assertInstanceOf('Oft\Admin\Form\GroupForm', $form);

        $elements = $form->getElements();
        foreach ($elements as $element) {
            $this->assertEquals('disabled', $element->getAttribute('disabled'));
        }
    }

    public function testInsert()
    {
        $group = Mockery::mock('Oft\Entity\GroupEntity');
        $group->shouldReceive('hasGroup')
            ->once()
            ->andReturn(false);
        $group->shouldReceive('save')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->insert($group);
    }

    /**
     * @expectedException DomainException
     */
    public function testInsertWithExistingRole()
    {
        $group = Mockery::mock('Oft\Entity\GroupEntity');
        $group->shouldReceive('hasGroup')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->insert($group);
    }

    public function testUpdate()
    {
        $group = Mockery::mock('Oft\Entity\GroupEntity');
        $group->shouldReceive('save')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->update($group);
    }

    public function testDelete()
    {
        $id = 1;

        $group = Mockery::mock('Oft\Entity\GroupEntity');
        $group->shouldReceive('load')
            ->with($id)
            ->once()
            ->andReturn(true);
        $group->shouldReceive('isUsed')
            ->once()
            ->andReturn(false);
        $group->shouldReceive('isDisallow')
            ->once()
            ->andReturn(false);
        $group->shouldReceive('deleteGroupResources')
            ->once()
            ->andReturn(true);
        $group->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->delete($group, $id);
    }

    /**
     * @expectedException DomainException
     */
    public function testDeleteWithUsedGroup()
    {
        $id = 1;

        $group = Mockery::mock('Oft\Entity\GroupEntity');
        $group->shouldReceive('load')
            ->with($id)
            ->once()
            ->andReturn(true);
        $group->shouldReceive('isUsed')
            ->once()
            ->andReturn(true);
        $group->shouldReceive('isDisallow')
            ->once()
            ->andReturn(false);

        $service = $this->getService();
        $service->delete($group, $id);
    }

    /**
     * @expectedException DomainException
     */
    public function testDeleteWithDisallowGroup()
    {
        $id = 1;

        $group = Mockery::mock('Oft\Entity\GroupEntity');
        $group->shouldReceive('load')
            ->with($id)
            ->once()
            ->andReturn(true);
        $group->shouldReceive('isDisallow')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->delete($group, $id);
    }

    public function testGetWhere()
    {
        $service = $this->getService();
        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityGroupForTestGetWhere');

        $this->assertEquals(array(), $service->fetchAll());
    }

    public function testGetWhereExceptAdmin()
    {
        $service = $this->getService();
        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityGroupForTestGetWhereExceptAdmin');

        $this->assertEquals(array(), $service->fetchAllExceptAdmin());
    }

    public function testGetById()
    {
        $service = $this->getService();
        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityGroupForTestGetById');

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

        $service->setEntityClassName('groups', 'Oft\Admin\Test\Service\MockEntityGroupsForAutocomplete');

        $expected = array('value1', 'value2', 'value3');
        $actual = $service->autoComplete('groups', 'field', 'value');

        $this->assertEquals($expected, $actual);
    }

}
