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

namespace Oft\Test\View\Helper;

class TestWidget
{
    public function __invoke($p1, $p2)
    {
        return $p1 . ' ' . $p2;
    }
}

class WidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Oft\View\Helper\Widget::__invoke
     */
    public function testInvoke()
    {
        $app = new \Oft\Mvc\Application(array(
            'widgets' => array(
                'test' => 'Oft\Test\View\Helper\TestWidget'
            ),
            'services' => array(
                'Widget' => 'Oft\Widget\WidgetFactory',
                'Log' => '\stdClass',
                'Translator' => '\stdClass',
            ),
            'debug' => true
        ));
        $app->init();
        
        $view = new \Oft\View\View();
        $view->setApplication($app);
        $app->setService('View', $view);

        $widgetHelper = new \Oft\View\Helper\Widget();
        $widgetHelper->setView($view);

        $result = $widgetHelper('test', array(1, 2));

        $this->assertSame('1 2', $result);
    }

}
