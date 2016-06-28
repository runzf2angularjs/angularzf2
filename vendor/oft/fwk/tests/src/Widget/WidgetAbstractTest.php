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

class ConcreteWidget extends \Oft\Widget\WidgetAbstract
{

    public function __invoke()
    {
        return true;
    }

    public function __call($methodName, $parameters)
    {
        $reflection = new \ReflectionObject($this);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this, $parameters);
    }

}

class WidgetAbstractTest extends \PHPUnit_Framework_TestCase
{

    public function getWidgetFactory()
    {
        $resolver = \Mockery::mock('Zend\View\Resolver\ResolverInterface');
        $resolver->shouldReceive('resolve')
            ->with('yop/laboom')
            ->andReturn(__DIR__ . '/_files/widget-abstract-test.phtml');

        $view = new \Oft\View\View();
        $view->setResolver($resolver);

        $app = new \Oft\Mvc\Application(array(
            'widgets' => array(),
        ));
        $app->setService('View', $view);

        $widgetFactory = new \Oft\Widget\WidgetFactory($app);

        return $widgetFactory;
    }

    public function testRenderWithArray()
    {
        $wf = $this->getWidgetFactory();

        $widget = new ConcreteWidget($wf);
        $result = $widget->render('yop/laboom', array(
            'a' => 1
        ));

        $expected = array(
            'a' => 1
        );
        
        $this->assertSame(print_r($expected, true), $result);
    }

    public function testRenderWithModel()
    {
        $model = new \Zend\View\Model\ViewModel();
        $model->b = 3;
        $model->c = 4;

        $app = $this->getWidgetFactory();

        $widget = new ConcreteWidget($app);
        $result = $widget->render('yop/laboom', $model);
        $expected = array(
            'b' => 3,
            'c' => 4,
        );
        
        $this->assertSame(print_r($expected, true), $result);
    }

}
