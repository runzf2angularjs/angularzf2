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

use DomainException;
use Mockery;
use Oft\Admin\Service\AclService;
use Oft\Mvc\Application;
use PHPUnit_Framework_TestCase;
use Zend\InputFilter\InputFilter;

class MockEntityAclForTestGetForm
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
        return array('groups' => array());
    }
}

class MockEntityRoleForTestGetListing
{
    public function fetchAllExceptAdmin()
    {
        return array(
            array(
                'id_acl_role' => 1
            ),
            array(
                'id_acl_role' => 2
            )
        );
    }
}

class MockEntityAclForTestGetListing
{
    public function fetchAll()
    {
        return array(
            array(
                'id_acl_resource' => 1,
                'id_acl_role' => 1
            ),
            array(
                'id_acl_resource' => 2,
                'id_acl_role' => 2
            )
        );
    }
}

class MockEntityResourceForTestGetListing
{
    public function fetchAll()
    {
        return array(
            array(
                'id_acl_resource' => 1,
                'name' => 'mvc.test',
            ),
            array(
                'id_acl_resource' => 2,
                'name' => 'mvc.tes',
            ),
            array(
                'id_acl_resource' => 3,
                'name' => 'mvc.test.index',
            )
        );
    }
}

class MockEntityAclForAutocomplete
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

class AclServiceTest extends PHPUnit_Framework_TestCase
{

    protected function getService(Application $app = null)
    {
        if ($app === null) {
            $app = new Application();
            $app->setService('Db', Mockery::mock('Doctrine\DBAL\Connection'));
        }

        return new AclService($app);
    }

    public function testGetFieldsSearch()
    {
        $service = $this->getService();

        $fieldsSearch = $service->getFieldsSearch();

        $this->assertInternalType('array', $fieldsSearch);
    }

    public function testGetListing()
    {
        $dataResult = array();
        
        $dataResult[1][1] = array('authorized' => true, 'herit' => null);
        $dataResult[1][2] = array('authorized' => false, 'herit' => null);
        $dataResult[2][1] = array('authorized' => false, 'herit' => null);
        $dataResult[2][2] = array('authorized' => true, 'herit' => null);
        $dataResult[3][1] = array('authorized' => true, 'herit' => 'mvc.test');
        $dataResult[3][2] = array('authorized' => false, 'herit' => null);

        $service = $this->getService();

        // Mocks des entités
        $service->setEntityClassName('resource', 'Oft\Admin\Test\Service\MockEntityResourceForTestGetListing');
        $service->setEntityClassName('acl', 'Oft\Admin\Test\Service\MockEntityAclForTestGetListing');
        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityRoleForTestGetListing');

        $data = $service->getListing();
        
        $this->assertEquals($dataResult, $data);
    }

    public function testGetSearchForm()
    {
        $service = $this->getService();
        
        $form = $service->getSearchForm();

        $this->assertTrue($form->has('group'));
        $this->assertTrue($form->has('resource'));
        $this->assertTrue($form->has('submitSearch'));
        $this->assertTrue($form->has('resetSearch'));

        $this->assertInstanceOf('Oft\Admin\Form\SearchForm', $form);
    }

    public function testGetForm()
    {
        $service = $this->getService();

        $form = $service->getForm();
        $resourceEntity = $form->getObject();

        $this->assertInstanceOf('Oft\Admin\Form\AclForm', $form);
        $this->assertInstanceOf('Oft\Entity\AclEntity', $resourceEntity);

        $this->assertTrue($resourceEntity->getInputFilter()->has('id_acl_resource'));
        $this->assertTrue($resourceEntity->getInputFilter()->has('id_acl_role'));
    }

    public function testGetFormWithId()
    {
        $service = $this->getService();

        $service->setEntityClassName('acl', 'Oft\Admin\Test\Service\MockEntityAclForTestGetForm');

        $form = $service->getForm(1);

        $this->assertInstanceOf('Oft\Admin\Form\AclForm', $form);
    }

    public function testInsert()
    {
        $acl = Mockery::mock('Oft\Entity\AclEntity');
        $acl->shouldReceive('hasAcl')
            ->once()
            ->andReturn(false);
        $acl->shouldReceive('insert')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->insert($acl);
    }

    /**
     * @expectedException DomainException
     */
    public function testInsertWithExistingRole()
    {
        $acl = Mockery::mock('Oft\Entity\AclEntity');
        $acl->shouldReceive('hasAcl')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->insert($acl);
    }

    public function testDelete()
    {
        $resourceId = 1;
        $roleId = 1;

        $resource = Mockery::mock('Oft\Entity\AclEntity');
        $resource->shouldReceive('load')
            ->with($resourceId, $roleId)
            ->once()
            ->andReturn(true);
        $resource->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->delete($resource, $resourceId, $roleId);
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

        $service->setEntityClassName('acl', 'Oft\Admin\Test\Service\MockEntityAclForAutocomplete');

        $expected = array('value1', 'value2', 'value3');
        $actual = $service->autoComplete('acl', 'field', 'value');

        $this->assertEquals($expected, $actual);
    }

}
