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

namespace Oft\Test\Widget;

class MenuWidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Oft\Widget\MenuWidget::__invoke
     */
    public function testInvoke()
    {
        $identity = new \Oft\Auth\Identity(array());

        $httpContext = new \Oft\Test\Mock\HttpContext();
        $httpContext->request->shouldReceive('getPathInfo')
            ->andReturn('/path/to/index.php');
        $httpContext->request->shouldReceive('getQueryString')
            ->andReturn('name=value');

        $resolver = \Mockery::mock('Zend\View\Resolver\ResolverInterface');
        $resolver->shouldReceive('resolve')
            ->with('oft/_widget/menu')
            ->andReturn(__DIR__ . '/_files/widget-abstract-test.phtml');

        $view = new \Oft\View\View();
        $view->setResolver($resolver);

        $app = new \Oft\Mvc\Application(array(
            'widgets' => array(),
        ));
        $app->setService('Menu', new \stdClass());
        $app->setService('Route', new \Oft\Mvc\Context\RouteContext());
        $app->setService('Identity', new \Oft\Test\Mock\IdentityContext($identity));
        $app->setService('Translator', new \Zend\I18n\Translator\Translator());
        $app->setService('Http', $httpContext);
        $app->setService('View', $view);

        // Fix Locale to avoid side effects
        $app->get('Translator')->setLocale('fr_FR');

        $widgetFactory = new \Oft\Widget\WidgetFactory($app);

        $menuWidget = new \Oft\Widget\MenuWidget($widgetFactory);

        $result = $menuWidget();

        $expected = array(
            'items' => new \stdClass(),
            'currentRoute' => array(),
            'replacements' => array(
                '%USERNAME%' => 'Guest',
                '%LOCALE%' => 'fr_FR',
                '%REQUEST_URI%' => '%2Fpath%2Fto%2Findex.php%3Fname%3Dvalue',
            ),
        );
        
        $this->assertSame(print_r($expected, true), $result);
    }

}
