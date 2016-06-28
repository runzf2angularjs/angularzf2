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

namespace Oft\Test\Service\Provider;

use Mockery;
use Oft\Auth\Identity;
use Oft\Mvc\Application;
use Oft\Mvc\Context\HttpContext;
use Oft\Service\Provider\Menu;
use PHPUnit_Framework_TestCase;
use stdClass;

class MenuProvider extends Menu
{
    public function __construct($identity, $availableLanguages = array('fr'), $router = null, $translator = null)
    {
        $this->identity = $identity;
        $this->availableLanguages = $availableLanguages;
        $this->router = $router;
        $this->translator = $translator;
    }
}

class MenuTest extends PHPUnit_Framework_TestCase
{

    public function getApplication($config)
    {
        $app = new Application($config);

        $request = Mockery::mock('Oft\Http\RequestInterface');
        $request->shouldReceive('getBaseUrl')
            ->withNoArgs()
            ->andReturn('/');

        $identityContext = \Mockery::mock('Oft\Mvc\Context\IdentityContext');
        $identityContext->shouldReceive('get')
            ->withNoArgs()
            ->andReturn(new Identity(array()));

        $app->setService('Acl', new stdClass());
        $app->setService('Router', new stdClass());
        $app->setService('Http', new HttpContext(array(
            'request' => $request
        )));
        $app->setService('Identity', $identityContext);
        $app->setService('Translator', new stdClass());

        return $app;
    }

    /**
     * @ covers Oft\Service\Provider\Menu::create
     */
    public function testCreate()
    {
        $menuProvider = new Menu();

        $config = array(
            'translator' => array(
                'default' => array(
                    'locale' => 'fr',
                ),
                'availableLanguages' => array(
                    'en'
                )
            ),
            'menu-bar' => array()
        );

        $app = $this->getApplication($config);
        
        $items = $menuProvider->create($app);

        $this->assertEmpty($items);
    }

    /**
     * @ covers Oft\Service\Provider\Menu::checkItemName
     */
    public function testCheckItemNameReturnTrueByDefault()
    {
        $identity = new Identity(array());
        $menuProvider = new MenuProvider($identity);

        $this->assertTrue($menuProvider->checkItemName('test'));
    }

    /**
     * @ covers Oft\Service\Provider\Menu::checkItemName
     */
    public function testCheckItemNameReturnFalseIfNotGestAndIsGuestName()
    {
        $identity = new Identity(array(
            'username' => 'test1234'
        ));
        $menuProvider = new MenuProvider($identity);

        $this->assertFalse($menuProvider->checkItemName('test:is-guest'));
    }

    /**
     * @ covers Oft\Service\Provider\Menu::checkItemName
     */
    public function testCheckItemNameReturnFalseGuestAndIsNotGuestName()
    {
        $identity = new Identity(array(
            'username' => Identity::GUEST_USERNAME
        ));
        $menuProvider = new MenuProvider($identity);

        $this->assertFalse($menuProvider->checkItemName('test:is-not-guest'));
    }

    /**
     * @ covers Oft\Service\Provider\Menu::checkItemName
     */
    public function testCheckItemNameReturnFalseAdminAndIsAdminName()
    {
        $identity = new Identity(array());
        $menuProvider = new MenuProvider($identity);

        $this->assertFalse($menuProvider->checkItemName('test:is-admin'));
    }

    /**
     * @ covers Oft\Service\Provider\Menu::getItem
     */
    public function testGetItemPassThru()
    {
        $item = array('key' => 'value');
        $identity = new Identity(array());
        $menuProvider = new MenuProvider($identity);

        $newItem = $menuProvider->getItem($item, 'test');

        $this->assertSame($item, $newItem);
    }

    /**
     * @ covers Oft\Service\Provider\Menu::getItem
     */
    public function testGetItemReturnFalse()
    {
        $item = array('key' => 'value');
        $identity = new Identity(array());
        $menuProvider = new MenuProvider($identity);

        $newItem = $menuProvider->getItem($item, 'test:is-admin');

        $this->assertFalse($newItem);
    }

    /**
     * @ covers Oft\Service\Provider\Menu::itemCompareCallback
     */
    public function testItemCompareCallbackBefore()
    {
        $item1 = array(
            'position' => 1,
        );
        $item2 = array(
            'position' => 2,
        );

        $identity = new Identity(array());
        $menuProvider = new MenuProvider($identity);

        $this->assertSame(-1, $menuProvider->itemCompareCallback($item1, $item2));
    }

    /**
     * @ covers Oft\Service\Provider\Menu::itemCompareCallback
     */
    public function testItemCompareCallbackSame()
    {
        $item1 = array(
            'position' => 1,
        );
        $item2 = array(
            'position' => 1,
        );

        $identity = new Identity(array());
        $menuProvider = new MenuProvider($identity);

        $this->assertSame(0, $menuProvider->itemCompareCallback($item1, $item2));
    }

    /**
     * @ covers Oft\Service\Provider\Menu::itemCompareCallback
     */
    public function testItemCompareCallbackAfter()
    {
        $item1 = array(
            'position' => 2,
        );
        $item2 = array(
            'position' => 1,
        );

        $identity = new Identity(array());
        $menuProvider = new MenuProvider($identity);

        $this->assertSame(1, $menuProvider->itemCompareCallback($item1, $item2));
    }

    /**
     * @ covers Oft\Service\Provider\Menu::getSubMenu
     */
    public function testGetSubMenu()
    {
        $identity = new Identity(array());
        $menuProvider = new MenuProvider($identity);

        $subMenu = $menuProvider->getSubMenu(array());

        $this->assertTrue($subMenu instanceof \Oft\Menu\Items);
        $this->assertSame(0, $subMenu->count());
    }

    /**
     * @ covers Oft\Service\Provider\Menu::getSubMenu
     */
    public function testGetSubMenuWithEmptyItem()
    {
        $identity = new Identity(array());
        $menuProvider = new MenuProvider($identity);

        $subMenu = $menuProvider->getSubMenu(array(
            'test' => null,
        ));

        $this->assertTrue($subMenu instanceof \Oft\Menu\Items);
        $this->assertSame(0, $subMenu->count());
    }

    /**
     * @ covers Oft\Service\Provider\Menu::getSubMenu
     */
    public function testGetSubMenuWithTwoItems()
    {
        $identity = new Identity(array());
        $menuProvider = new MenuProvider($identity);

        $subMenu = $menuProvider->getSubMenu(array(
            'test2' => array(
                'position' => 2,
            ),
            'test1' => array(
                'position' => 1,
            ),
        ));

        $this->assertTrue($subMenu instanceof \Oft\Menu\Items);
        $this->assertSame(2, $subMenu->count());

        $count = 0;
        foreach ($subMenu as $key => $value) {
            if ($count == 0) {
                $this->assertSame('test1', $key);
            } else if ($count == 1) {
                $this->assertSame('test2', $key);
            } else {
                $this->fail('No more than 2 items should be returned');
            }
            $count ++;
        }
    }

    /**
     * @ covers Oft\Service\Provider\Menu::getLangMenu
     */
    public function testGetLangMenuReturnFalseIfNotEnougthAvailableLanguage()
    {
        $identity = new Identity(array());
        $menuProvider = new MenuProvider($identity, array());

        $langMenu = $menuProvider->getLangMenu();

        $this->assertFalse($langMenu);
    }

    /**
     * @ covers Oft\Service\Provider\Menu::getLangMenu
     */
    public function testGetLangMenuReturnSubMenu()
    {
        $identity = new Identity(array());

        $routerFactory = new \Aura\Router\RouterFactory();
        $router = $routerFactory->newInstance();

        $router->getRoutes()->add('user.language', '/user/lang');

        $translator = new \Zend\I18n\Translator\Translator();

        $menuProvider = new MenuProvider($identity, array('fr', 'en'), $router, $translator);

        $langMenu = $menuProvider->getLangMenu();

        $this->assertTrue(is_array($langMenu));
        $this->assertTrue(isset($langMenu['name']));
        $this->assertCount(2, $langMenu['submenu']);
    }

}
