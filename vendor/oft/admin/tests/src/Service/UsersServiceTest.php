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

use DateTime;
use Doctrine\DBAL\Query\QueryBuilder;
use DomainException;
use Mockery;
use Oft\Admin\Form\PasswordForm;
use Oft\Admin\Service\UsersService;
use Oft\Auth\Identity;
use Oft\Mvc\Application;
use Oft\Mvc\Context\IdentityContext;
use PHPUnit_Framework_TestCase;
use Zend\I18n\Translator\Translator;
use Zend\InputFilter\InputFilter;

class MockEntityUserForTestGetPaginator
{
    public function getQueryBuilder()
    {
        $qb = Mockery::mock('Doctrine\DBAL\Query\QueryBuilder');
        $qb->shouldReceive('getType')->once()->andReturn(QueryBuilder::SELECT);
        return $qb;
    }
}

class MockEntityUserRoleForTestGetForm
{
    public function getSelectValues()
    {
        return array();
    }
}

class MockEntityUserForTestGetForm
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

class MockEntityUserForTestForgotPassword
{
    public function loadByUsername($username)
    {
        return true;
    }
    public function getArrayCopy()
    {
        return array('username' => 'username', 'mail' => 'test@test.com');
    }
    public function generateToken()
    {
        return 'token';
    }
}

class MockEntityUserForTestGetEmailInUsername
{
    public function loadByUsername($username)
    {
        return true;
    }
    public function getArrayCopy()
    {
        return array('username' => 'email@test.com', 'mail' => '@test.com');
    }
}

class MockEntityUserForTestGetEmailFailed
{
    public function loadByUsername($username)
    {
        throw new DomainException();
    }
    public function getArrayCopy()
    {
        return array('username' => 'username', 'mail' => '@test.com');
    }
}

class MockEntityUserForTestGetEmailWithInvalidMail
{
    public function loadByUsername($username)
    {
        return true;
    }
    public function getArrayCopy()
    {
        return array('username' => 'username', 'mail' => 'invalid-mail');
    }
}

class MockEntityUserForTestChangePassword
{
    public function loadByUsername($username)
    {
        return true;
    }
    public function getArrayCopy()
    {
        return array(
            'salt' => '7bfaa4da',
            'password' => 'ca60c887b876b7a7c7b32aea84047496'
        );
    }
    public function exchangeArray($array)
    {
    }
    public function save()
    {
        return true;
    }
    public function resetToken()
    {
        return true;
    }
}

class MockEntityUserForTestIsValidToken
{
    public function loadByUsername($username)
    {
        return true;
    }
    public function getArrayCopy()
    {
        $now = new DateTime();
        $now->modify('+ 12 hours');

        return array(
            'token' => 'test',
            'token_date' => $now->format('Y-m-d H:i:s')
        );
    }
}

class MockEntityUserForTestIsValidTokenWithExpiredDate
{
    public function loadByUsername($username)
    {
        return true;
    }
    public function getArrayCopy()
    {
        $now = new DateTime();
        $now->modify('- 12 hours');

        return array(
            'token' => 'test',
            'token_date' => $now->format('Y-m-d H:i:s')
        );
    }
}

class MockEntityGroupForGetIdGroup
{
    public function getByName()
    {
        return array('id_acl_role' => 1);
    }
}

class MockMail
{
    public function send()
    {
        return true;
    }
}

class MockIdentityContext extends IdentityContext
{
    public function __construct(Identity $identity)
    {
        $session = \Mockery::mock('Oft\Http\SessionInterface');
        parent::__construct($session, 3600);

        $this->identity = $identity;
    }
}

class MockEntityUserForAutocomplete
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

class UsersServiceTest extends PHPUnit_Framework_TestCase
{

    protected function getService(Application $app = null)
    {
        if ($app === null) {
            $config = array('application' => array('name' => 'test'));

            $moduleManager = Mockery::mock('Oft\Module\ModuleManager');
            $moduleManager->shouldReceive('getModules')
                ->andReturn(array('module1', 'module2'));

            $app = new Application($config, $moduleManager);
            $app->setService('Db', Mockery::mock('Doctrine\DBAL\Connection'));
            $app->setService('View', Mockery::mock('Oft\View\View'));
            $app->setService('Translator', new Translator());
            $app->setService('Identity', new MockIdentityContext(new Identity(array())));
        }

        return new UsersService($app);
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

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestGetPaginator');

        $paginator = $service->getPaginator(array('username' => 'test', 'givenname' => 'test'));
        
        $this->assertInstanceOf('Zend\Paginator\Paginator', $paginator);
    }

    public function testGetSearchForm()
    {
        $service = $this->getService();
        
        $form = $service->getSearchForm();

        $this->assertTrue($form->has('username'));
        $this->assertTrue($form->has('givenname'));
        $this->assertTrue($form->has('surname'));
        $this->assertTrue($form->has('active'));
        $this->assertTrue($form->has('submitSearch'));
        $this->assertTrue($form->has('resetSearch'));

        $this->assertInstanceOf('Oft\Admin\Form\SearchForm', $form);
    }

    public function testGetForm()
    {
        $service = $this->getService();

        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityUserRoleForTestGetForm');

        $form = $service->getForm();

        $this->assertInstanceOf('Oft\Admin\Form\UserForm', $form);
        $this->assertInstanceOf('Oft\Entity\UserEntity', $form->getObject());

        $userEntity = $form->getObject();

        $this->assertTrue($userEntity->getInputFilter()->get('password')->isRequired());
        $this->assertTrue($userEntity->getInputFilter()->get('password_confirm')->isRequired());
        $this->assertFalse($userEntity->getInputFilter()->has('id_user'));
    }

    public function testGetFormWithId()
    {
        $service = $this->getService();

        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityUserRoleForTestGetForm');
        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestGetForm');

        $form = $service->getFormReadOnly(1);

        $this->assertInstanceOf('Oft\Admin\Form\UserForm', $form);

        $elements = $form->getElements();
        foreach ($elements as $element) {
            $this->assertEquals('disabled', $element->getAttribute('disabled'));
        }
    }

    public function testInsert()
    {
        $user = Mockery::mock('Oft\Entity\UserEntity');
        $user->shouldReceive('hasUser')
            ->once()
            ->andReturn(false);
        $user->shouldReceive('save')
            ->once()
            ->andReturn(true);
        $user->shouldReceive('getArrayCopy')
            ->once()
            ->andReturn(array('groups' => array()));
        $user->shouldReceive('getGroups')
            ->once()
            ->andReturn(array());

        $service = $this->getService();
        $service->insert($user);
    }

    /**
     * @expectedException DomainException
     */
    public function testInsertWithExistingCUID()
    {
        $user = Mockery::mock('Oft\Entity\UserEntity');
        $user->shouldReceive('hasUser')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->insert($user);
    }

    public function testUpdate()
    {
        $user = Mockery::mock('Oft\Entity\UserEntity');
        $user->shouldReceive('save')
            ->once()
            ->andReturn(true);
        $user->shouldReceive('getArrayCopy')
            ->once()
            ->andReturn(array('groups' => array()));
        $user->shouldReceive('getGroups')
            ->once()
            ->andReturn(array());

        $service = $this->getService();
        $service->update($user);
    }

    public function testDelete()
    {
        $user = Mockery::mock('Oft\Entity\UserEntity');
        $user->shouldReceive('load')
            ->once()
            ->andReturn(true);
        $user->shouldReceive('getGroups')
            ->once()
            ->andReturn(array('test'));
        $user->shouldReceive('delete')
            ->once()
            ->andReturn(true);
        $user->shouldReceive('removeGroup')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityGroupForGetIdGroup');
        $service->delete($user, 1);
    }

    public function testRemoveRole()
    {
        $user = Mockery::mock('Oft\Entity\UserEntity');
        $user->shouldReceive('removeGroup')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityGroupForGetIdGroup');
        $service->removeGroup($user, 'test');
    }

    public function testAddRole()
    {
        $user = Mockery::mock('Oft\Entity\UserEntity');
        $user->shouldReceive('addGroup')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityGroupForGetIdGroup');
        $service->addGroup($user, 'test');
    }

    public function testSaveRoles()
    {
        $user = Mockery::mock('Oft\Entity\UserEntity');
        $user->shouldReceive('getArrayCopy')
            ->once()
            ->andReturn(array('groups' => array('test1' => 'test1')));

        $user->shouldReceive('getGroups')
            ->once()
            ->andReturn(array('test2' => 'test2'));

        $user->shouldReceive('removeGroup')
            ->once()
            ->andReturn(true);

        $user->shouldReceive('addGroup')
            ->once()
            ->andReturn(true);

        $service = $this->getService();
        $service->setEntityClassName('group', 'Oft\Admin\Test\Service\MockEntityGroupForGetIdGroup');
        $service->saveGroups($user);
    }

    public function testGetCivilities()
    {
        $service = $this->getService();
        
        $data = array(
            0 => '-',
            1 => 'Mr',
            2 => 'Mrs',
            3 => 'Ms',
        );

        $this->assertEquals($data, $service->getCivilities());
    }

    public function testGetPasswordForm()
    {
        $service = $this->getService();

        $form = $service->getFormPassword();

        $elements = $form->getElements();

        $this->assertInstanceOf('Oft\Admin\Form\PasswordForm', $form);
        $this->assertTrue(isset($elements['password']));
        $this->assertTrue(isset($elements['new_password']));
        $this->assertTrue(isset($elements['new_password_confirm']));
    }

    public function testGetPasswordResetForm()
    {
        $service = $this->getService();

        /* @var $form PasswordForm */
        $form = $service->getFormPasswordReset('test');

        $elements = $form->getElements();

        $this->assertInstanceOf('Oft\Admin\Form\PasswordForm', $form);
        $this->assertFalse(isset($elements['password']));
        $this->assertTrue(isset($elements['new_password']));
        $this->assertTrue(isset($elements['new_password_confirm']));
    }

    public function testChangePassword()
    {
        $service = $this->getService();

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestChangePassword');

        $this->assertTrue($service->changePassword('test', 'password', 'new'));
    }

    /**
     * @expectedException DomainException
     */
    public function testChangePasswordWithBadPassword()
    {
        $service = $this->getService();

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestChangePassword');

        $this->assertTrue($service->changePassword('test', 'badPassword', 'new'));
    }

    public function testChangePasswordForced()
    {
        $service = $this->getService();

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestChangePassword');

        $this->assertTrue($service->changePassword('test', null, 'new', true));
    }

    public function testForgotPassword()
    {
        $view = Mockery::mock('Oft\View\View');
        $view->shouldReceive('setResolver')
            ->once()
            ->andReturn($view);
        $view->shouldReceive('render')
            ->once()
            ->andReturn("message");

        $identity = new Identity(array());

        $mockIdentityContext = new MockIdentityContext($identity);

        $connection = \Mockery::mock('Doctrine\DBAL\Connection');
        $connection->shouldReceive('getConfiguration')
            ->withNoArgs()
            ->andReturn(null);

        $config = array(
            'application' => array(
                'name' => 'test',
                'contact' => array(
                    'mail' => 'test@test.com'
                ),
            ),
        );

        $translator = new Translator();
        
        $app = new Application($config);
        $app->setService('Db', $connection);
        $app->setService('Identity', $mockIdentityContext);
        $app->setService('View', $view);
        $app->setService('Translator', $translator);

        $service = $this->getService($app);

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestForgotPassword');
        $service->setTransportClassName('Oft\Admin\Test\Service\MockMail');

        $service->forgotPassword('test');
    }

    /**
     * @expectedException DomainException
     */
    public function testGetEmailFailed()
    {
        $service = $this->getService();

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestGetEmailFailed');

        $service->getEmail('test');
    }

    public function testGetEmailInUsername()
    {
        $service = $this->getService();

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestGetEmailInUsername');

        $email = $service->getEmail('test');
        
        $this->assertEquals('email@test.com', $email);
    }

    /**
     * @expectedException DomainException
     */
    public function testGetEmailWithInvalidMail()
    {
        $service = $this->getService();

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestGetEmailWithInvalidMail');

        $service->getEmail('test');
    }

    public function testIsValidToken()
    {
        $service = $this->getService();

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestIsValidToken');

        $this->assertTrue($service->isValidToken('user', 'test'));
    }

    public function testIsValidTokenExpired()
    {
        $service = $this->getService();

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestIsValidTokenWithExpiredDate');

        $this->assertFalse($service->isValidToken('user', 'test'));
    }
    
    public function testIsValidTokenWithWrongKey()
    {
        $service = $this->getService();

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForTestIsValidToken');

        $this->assertFalse($service->isValidToken('user', 'testWrongKey'));
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

        $service->setEntityClassName('user', 'Oft\Admin\Test\Service\MockEntityUserForAutocomplete');

        $expected = array('value1', 'value2', 'value3');
        $actual = $service->autoComplete('user', 'field', 'value');

        $this->assertEquals($expected, $actual);
    }

}
